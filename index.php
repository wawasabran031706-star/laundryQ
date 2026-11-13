<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="description"
        content="LaundryQ makes laundry orders, reservations, and pickup tracking simple."
    >
    <title>LaundryQ | Fresh laundry, made simple</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <style>
        :root {
            color-scheme: dark;
            --aqua: #61d9ee;
            --ink: #062238;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: #fff;
            background-color: var(--ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        .landing {
            position: relative;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            overflow: hidden;
            isolation: isolate;
            background-image:
                linear-gradient(
                    105deg,
                    rgba(2, 20, 38, .90) 0%,
                    rgba(3, 35, 57, .70) 48%,
                    rgba(3, 33, 54, .24) 100%
                ),
                linear-gradient(
                    0deg,
                    rgba(2, 19, 34, .62),
                    transparent 58%
                ),
                url("assets/images/laundry-background.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .landing::before {
            position: absolute;
            z-index: -1;
            inset: 0;
            content: "";
            background:
                radial-gradient(
                    circle at 74% 44%,
                    rgba(64, 207, 238, .15),
                    transparent 34rem
                );
        }

        .topbar {
            display: flex;
            width: min(1120px, calc(100% - 3rem));
            align-items: center;
            justify-content: space-between;
            margin: 0 auto;
            padding: 1.6rem 0;
        }

        .brand {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 750;
            letter-spacing: -.04em;
            text-decoration: none;
        }

        .brand-mark {
            display: inline-grid;
            width: 2.45rem;
            height: 2.45rem;
            place-items: center;
            margin-right: .55rem;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: .85rem;
            background: rgba(91, 214, 237, .17);
            color: var(--aqua);
            font-size: 1.2rem;
            vertical-align: middle;
        }

        .staff-link {
            color: rgba(255, 255, 255, .82);
            font-size: .9rem;
            text-decoration: none;
            transition: color .2s ease;
        }

        .staff-link:hover,
        .staff-link:focus-visible {
            color: var(--aqua);
        }

        .hero {
            display: flex;
            width: min(1120px, calc(100% - 3rem));
            flex: 1;
            align-items: center;
            margin: 0 auto;
            padding: 2.5rem 0 5rem;
        }

        .hero-copy {
            max-width: 610px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: 1.25rem;
            color: #a7f0fa;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 1.7rem;
            height: 1px;
            background: var(--aqua);
            content: "";
        }

        h1 {
            max-width: 620px;
            margin: 0;
            font-size: clamp(3rem, 7vw, 5.6rem);
            font-weight: 750;
            letter-spacing: -.065em;
            line-height: .99;
        }

        h1 span {
            color: var(--aqua);
        }

        .hero-description {
            max-width: 480px;
            margin: 1.5rem 0 2rem;
            color: rgba(239, 249, 255, .82);
            font-size: clamp(1rem, 2vw, 1.15rem);
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
        }

        .actions .btn {
            min-width: 155px;
            padding: .8rem 1.3rem;
            border-radius: .8rem;
            font-weight: 650;
        }

        .btn-aqua {
            border: 1px solid var(--aqua);
            background: var(--aqua);
            color: #062238;
        }

        .btn-aqua:hover,
        .btn-aqua:focus-visible {
            border-color: #9aeeFA;
            background: #9aeeFA;
            color: #062238;
        }

        .btn-glass {
            border: 1px solid rgba(255, 255, 255, .55);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            backdrop-filter: blur(8px);
        }

        .btn-glass:hover,
        .btn-glass:focus-visible {
            border-color: #fff;
            background: rgba(255, 255, 255, .17);
            color: #fff;
        }

        .hero-note {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-top: 2rem;
            color: rgba(239, 249, 255, .76);
            font-size: .9rem;
        }

        .note-dot {
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: var(--aqua);
            box-shadow: 0 0 14px rgba(97, 217, 238, .8);
        }

        @media (max-width: 575.98px) {
            .topbar,
            .hero {
                width: min(100% - 2rem, 1120px);
            }

            .landing {
                background-position: 58% center;
            }

            .hero {
                align-items: flex-end;
                padding: 3rem 0 4rem;
            }

            .hero-copy {
                padding: 1.4rem;
                border: 1px solid rgba(255, 255, 255, .18);
                border-radius: 1.25rem;
                background: rgba(3, 27, 45, .55);
                backdrop-filter: blur(8px);
            }

            h1 {
                font-size: clamp(2.8rem, 14vw, 4rem);
            }

            .actions {
                flex-direction: column;
            }

            .actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="landing">
        <header class="topbar">
            <a class="brand" href="index.php" aria-label="LaundryQ home">
                <span class="brand-mark" aria-hidden="true">L</span>
                LaundryQ
            </a>
            <a class="staff-link" href="admin/login.php">Staff login <span aria-hidden="true">↗</span></a>
        </header>

        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-copy">
                <div class="eyebrow">Laundry day, simplified</div>
                <h1 id="hero-title">
                    Fresh clothes.<br>
                    <span>More free time.</span>
                </h1>
                <p class="hero-description">
                    Place your laundry order, reserve a drop-off, and follow
                    every update from one easy place.
                </p>
                <div class="actions">
                    <a class="btn btn-aqua" href="customer/register.php">
                        Create an account
                    </a>
                    <a class="btn btn-glass" href="customer/login.php">
                        Customer login
                    </a>
                </div>
                <div class="hero-note">
                    <span class="note-dot" aria-hidden="true"></span>
                    Simple ordering. Clear pickup updates.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
