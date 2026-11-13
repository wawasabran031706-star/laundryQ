<?php
// File: laundryq/config/email.php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Configuration helpers
|--------------------------------------------------------------------------
*/

function environmentValue(
    string $name,
    string $default = ''
): string {
    $value = getenv($name);

    if (
        $value === false ||
        trim($value) === ''
    ) {
        return $default;
    }

    return trim($value);
}

function laundryqBaseUrl(): string
{
    return rtrim(
        environmentValue(
            'APP_URL',
            'http://localhost:8000'
        ),
        '/'
    );
}

/*
|--------------------------------------------------------------------------
| Brevo HTTPS email sender
|--------------------------------------------------------------------------
*/

function sendBrevoEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlContent,
    string $textContent
): bool {
    if (
        $recipientEmail === '' ||
        !filter_var(
            $recipientEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        error_log(
            'Brevo email skipped: invalid recipient email.'
        );

        return false;
    }

    $apiKey = environmentValue(
        'BREVO_API_KEY'
    );

    $senderEmail = environmentValue(
        'MAIL_FROM_ADDRESS'
    );

    $senderName = environmentValue(
        'MAIL_FROM_NAME',
        'LaundryQ'
    );

    if ($apiKey === '') {
        error_log(
            'Brevo email failed: BREVO_API_KEY is missing.'
        );

        return false;
    }

    if (
        $senderEmail === '' ||
        !filter_var(
            $senderEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        error_log(
            'Brevo email failed: sender email is invalid.'
        );

        return false;
    }

    $payload = json_encode(
        [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail
            ],
            'to' => [
                [
                    'email' => $recipientEmail,
                    'name' => $recipientName
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
            'textContent' => $textContent
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($payload === false) {
        error_log(
            'Brevo email failed: payload encoding error.'
        );

        return false;
    }

    $curl = curl_init(
        'https://api.brevo.com/v3/smtp/email'
    );

    if ($curl === false) {
        error_log(
            'Brevo email failed: cURL could not initialize.'
        );

        return false;
    }

    curl_setopt_array(
        $curl,
        [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json'
            ],
            CURLOPT_POSTFIELDS => $payload
        ]
    );

    $response = curl_exec($curl);

    $curlError = curl_error($curl);

    $httpCode = (int) curl_getinfo(
        $curl,
        CURLINFO_HTTP_CODE
    );

    curl_close($curl);

    if ($response === false) {
        error_log(
            'Brevo email cURL error: ' .
            $curlError
        );

        return false;
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300
    ) {
        error_log(
            'Brevo email API error. HTTP ' .
            $httpCode .
            ': ' .
            $response
        );

        return false;
    }

    return true;
}

function sendSmtpEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlContent,
    string $textContent,
    string $senderEmail,
    string $senderName
): bool {
    $host = environmentValue('SMTP_HOST');
    $portValue = environmentValue('SMTP_PORT', '587');
    $username = environmentValue('SMTP_USERNAME');
    $password = environmentValue('SMTP_PASSWORD');
    $encryption = strtolower(
        environmentValue('SMTP_ENCRYPTION', 'tls')
    );

    if ($host === '') {
        error_log('SMTP email failed: SMTP_HOST is missing.');
        return false;
    }

    if (
        !ctype_digit($portValue) ||
        (int) $portValue < 1 ||
        (int) $portValue > 65535
    ) {
        error_log('SMTP email failed: SMTP_PORT is invalid.');
        return false;
    }

    $encryptionOptions = [
        'tls' => PHPMailer::ENCRYPTION_STARTTLS,
        'starttls' => PHPMailer::ENCRYPTION_STARTTLS,
        'ssl' => PHPMailer::ENCRYPTION_SMTPS,
        'smtps' => PHPMailer::ENCRYPTION_SMTPS,
        'none' => ''
    ];

    if (!array_key_exists($encryption, $encryptionOptions)) {
        error_log(
            'SMTP email failed: SMTP_ENCRYPTION must be tls, ssl, or none.'
        );
        return false;
    }

    if (($username === '') !== ($password === '')) {
        error_log(
            'SMTP email failed: SMTP_USERNAME and SMTP_PASSWORD must both be set.'
        );
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int) $portValue;
        $mail->SMTPAuth = $username !== '';
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $encryptionOptions[$encryption];
        $mail->SMTPAutoTLS = $encryption !== 'none';
        $mail->Timeout = 25;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($senderEmail, $senderName);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        $mail->AltBody = $textContent;
        $mail->send();

        return true;
    } catch (Throwable $e) {
        error_log(
            'SMTP email failed: ' . $e->getMessage()
        );

        return false;
    }
}

function sendLaundryQEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlContent,
    string $textContent
): bool {
    $transport = strtolower(
        environmentValue('MAIL_TRANSPORT')
    );

    if ($transport === '') {
        $transport = environmentValue('SMTP_HOST') !== ''
            ? 'smtp'
            : 'brevo';
    }

    if ($transport === 'brevo') {
        return sendBrevoEmail(
            $recipientEmail,
            $recipientName,
            $subject,
            $htmlContent,
            $textContent
        );
    }

    if ($transport !== 'smtp') {
        error_log(
            'Email failed: MAIL_TRANSPORT must be smtp or brevo.'
        );

        return false;
    }

    $senderEmail = environmentValue('MAIL_FROM_ADDRESS');

    if (
        $senderEmail === '' ||
        !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)
    ) {
        error_log('SMTP email failed: sender email is invalid.');
        return false;
    }

    return sendSmtpEmail(
        $recipientEmail,
        $recipientName,
        $subject,
        $htmlContent,
        $textContent,
        $senderEmail,
        environmentValue('MAIL_FROM_NAME', 'LaundryQ')
    );
}

/*
|--------------------------------------------------------------------------
| Verification email
|--------------------------------------------------------------------------
*/

function sendVerificationEmail(
    string $email,
    string $fullName,
    string $verificationToken
): bool {
    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        error_log(
            'Verification email skipped: invalid email.'
        );

        return false;
    }

    $verificationUrl =
        laundryqBaseUrl() .
        '/customer/verify_email.php?token=' .
        urlencode($verificationToken);

    $safeName = htmlspecialchars(
        $fullName,
        ENT_QUOTES,
        'UTF-8'
    );

    $safeUrl = htmlspecialchars(
        $verificationUrl,
        ENT_QUOTES,
        'UTF-8'
    );

    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="
    margin:0;
    padding:24px;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
    line-height:1.6;
    color:#212529;
">
    <div style="
        max-width:560px;
        margin:auto;
        padding:28px;
        background:#ffffff;
        border-radius:12px;
    ">
        <h2 style="color:#0d6efd;">
            Verify your LaundryQ account
        </h2>

        <p>
            Hello <strong>{$safeName}</strong>,
        </p>

        <p>
            Please verify your email address by clicking
            the button below.
        </p>

        <p>
            <a
                href="{$safeUrl}"
                target="_blank"
                rel="noopener noreferrer"
                style="
                    display:inline-block;
                    padding:12px 20px;
                    color:#ffffff;
                    background:#0d6efd;
                    text-decoration:none;
                    border-radius:6px;
                    font-weight:bold;
                "
            >
                Verify Email Address
            </a>
        </p>

        <p>
            If the button does not work, copy and paste
            this link into your browser:
        </p>

        <p style="
            word-break:break-all;
            font-size:13px;
            color:#495057;
        ">
            {$safeUrl}
        </p>

        <p>
            This link expires in 24 hours.
        </p>

        <p>
            Thank you,<br>
            <strong>LaundryQ Team</strong>
        </p>
    </div>
</body>
</html>
HTML;

    $textContent =
        "Hello {$fullName},\n\n" .
        "Verify your LaundryQ account using this link:\n" .
        $verificationUrl . "\n\n" .
        "This link expires in 24 hours.\n\n" .
        "LaundryQ Team";

    return sendLaundryQEmail(
        $email,
        $fullName,
        'Verify your LaundryQ account',
        $htmlContent,
        $textContent
    );
}

/*
|--------------------------------------------------------------------------
| Reservation status email
|--------------------------------------------------------------------------
*/

function sendReservationStatusEmail(
    string $email,
    string $fullName,
    string $reservationId,
    string $status
): bool {
    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        error_log(
            'Reservation status email skipped: invalid email.'
        );

        return false;
    }

    $safeName = htmlspecialchars(
        $fullName,
        ENT_QUOTES,
        'UTF-8'
    );

    $safeReservationId = htmlspecialchars(
        $reservationId,
        ENT_QUOTES,
        'UTF-8'
    );

    $safeStatus = htmlspecialchars(
        $status,
        ENT_QUOTES,
        'UTF-8'
    );

    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="
    margin:0;
    padding:24px;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
    color:#212529;
">
    <div style="
        max-width:560px;
        margin:auto;
        padding:28px;
        background:#ffffff;
        border-radius:12px;
    ">
        <h2 style="color:#0d6efd;">
            LaundryQ Reservation Update
        </h2>

        <p>
            Hello <strong>{$safeName}</strong>,
        </p>

        <p>
            Your reservation status was updated.
        </p>

        <div style="
            margin:24px 0;
            padding:18px;
            border-left:5px solid #0d6efd;
            background:#f0f6ff;
        ">
            <p>
                <strong>Reservation:</strong>
                #{$safeReservationId}
            </p>

            <p>
                <strong>New status:</strong>
                <span style="color:#0d6efd;">
                    {$safeStatus}
                </span>
            </p>
        </div>

        <p>
            Please log in to LaundryQ for more details.
        </p>

        <p>
            Thank you,<br>
            <strong>LaundryQ Team</strong>
        </p>
    </div>
</body>
</html>
HTML;

    $textContent =
        "Hello {$fullName},\n\n" .
        "Your LaundryQ reservation " .
        "#{$reservationId} status is now: " .
        "{$status}.\n\n" .
        "LaundryQ Team";

    return sendLaundryQEmail(
        $email,
        $fullName,
        'LaundryQ reservation status update',
        $htmlContent,
        $textContent
    );
}

/*
|--------------------------------------------------------------------------
| Order status email
|--------------------------------------------------------------------------
*/

function sendOrderStatusEmail(
    string $email,
    string $fullName,
    string $orderId,
    string $status
): bool {
    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        error_log(
            'Order status email skipped: invalid email.'
        );

        return false;
    }

    $safeName = htmlspecialchars(
        $fullName,
        ENT_QUOTES,
        'UTF-8'
    );

    $safeOrderId = htmlspecialchars(
        $orderId,
        ENT_QUOTES,
        'UTF-8'
    );

    $safeStatus = htmlspecialchars(
        $status,
        ENT_QUOTES,
        'UTF-8'
    );

    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="
    margin:0;
    padding:24px;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
    line-height:1.6;
    color:#212529;
">
    <div style="
        max-width:560px;
        margin:auto;
        padding:28px;
        background:#ffffff;
        border-radius:12px;
    ">
        <h2 style="color:#0d6efd;">
            LaundryQ Order Status Update
        </h2>

        <p>
            Hello <strong>{$safeName}</strong>,
        </p>

        <p>
            Your laundry order status has been updated.
        </p>

        <div style="
            margin:24px 0;
            padding:18px;
            border-left:5px solid #0d6efd;
            background:#f0f6ff;
        ">
            <p>
                <strong>Order:</strong>
                #{$safeOrderId}
            </p>

            <p>
                <strong>New status:</strong>
                <span style="color:#0d6efd;">
                    {$safeStatus}
                </span>
            </p>
        </div>

        <p>
            Please log in to LaundryQ for more details.
        </p>

        <p>
            Thank you,<br>
            <strong>LaundryQ Team</strong>
        </p>
    </div>
</body>
</html>
HTML;

    $textContent =
        "Hello {$fullName},\n\n" .
        "Your LaundryQ order #{$orderId} status " .
        "is now: {$status}.\n\n" .
        "Please log in to LaundryQ for more details.\n\n" .
        "LaundryQ Team";

    return sendLaundryQEmail(
        $email,
        $fullName,
        'LaundryQ order status update',
        $htmlContent,
        $textContent
    );
}
