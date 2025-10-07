<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stellar - Découvrez l'Univers en Direct</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-soft: rgba(99, 102, 241, 0.18);
            --accent: #f472b6;
            --sky: #38bdf8;
            --deep: rgba(8, 11, 32, 0.85);
            --glass: rgba(17, 20, 45, 0.35);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text: rgba(255, 255, 255, 0.92);
            --text-soft: rgba(255, 255, 255, 0.68);
            --shadow: 0 30px 80px rgba(8, 11, 32, 0.45);
            --radius-lg: 28px;
            --radius-md: 20px;
            --max-width: 1240px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "SF Pro Display", system-ui, sans-serif;
            background: #050611;
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow-x: hidden;
        }

        a {
            color: inherit;
        }

        .bg-image {
            position: fixed;
            inset: 0;
            background: url('/img/welcome/background.jpg') center/cover no-repeat;
            z-index: -10;
        }

        .bg-overlay {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 120% 10%, rgba(99, 102, 241, 0.35), transparent 60%),
                radial-gradient(ellipse at -10% 40%, rgba(168, 85, 247, 0.35), transparent 60%),
                linear-gradient(180deg, rgba(4, 6, 18, 0.85), rgba(4, 6, 18, 0.94));
            z-index: -9;
        }

        #stars {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -8;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.4rem 3rem;
            z-index: 100;
            transition: background 0.4s ease, box-shadow 0.4s ease, padding 0.4s ease;
        }

        nav.scrolled {
            background: rgba(7, 8, 22, 0.85);
            backdrop-filter: blur(18px);
            box-shadow: 0 12px 40px rgba(5, 6, 17, 0.35);
            padding: 1rem 3rem;
        }

        .nav-container {
            width: 100%;
            max-width: var(--max-width);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .brand {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.18em;
        }

        .nav-links {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.92rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            position: relative;
            padding-bottom: 0.4rem;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .nav-links a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .btn-nav {
            padding: 0.75rem 1.6rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(13, 15, 33, 0.5);
            color: var(--text);
            font-size: 0.86rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }

        .btn-nav:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.15);
            color: #050611;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.35);
        }

        main {
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 8rem 2.5rem 5rem;
        }

        section {
            margin-bottom: 5.5rem;
        }

        .hero {
            min-height: 95vh;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            align-items: center;
        }

        .hero-content {
            grid-column: 1 / span 7;
            display: flex;
            flex-direction: column;
            gap: 1.8rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .hero-title {
            font-size: clamp(3.5rem, 6vw, 5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff 0%, var(--sky) 40%, var(--accent) 65%, #ffffff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 220% 220%;
            animation: titleGlow 16s ease infinite;
        }

        @keyframes titleGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-text {
            font-size: 1.1rem;
            color: var(--text-soft);
            max-width: 540px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-primary {
            padding: 1rem 2.6rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            box-shadow: 0 24px 60px rgba(99, 102, 241, 0.35);
            cursor: pointer;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .btn-primary:hover {
            transform: translateY(-6px);
            box-shadow: 0 32px 70px rgba(244, 114, 182, 0.38);
        }

        .btn-ghost {
            padding: 1rem 1.6rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(15, 17, 43, 0.45);
            color: var(--text);
            letter-spacing: 0.08em;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-ghost:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.3);
        }

        .hero-visual {
            grid-column: 8 / span 5;
            position: relative;
            display: grid;
            gap: 1.5rem;
        }

        .metric-card {
            padding: 1.2rem 1.5rem;
            border-radius: var(--radius-md);
            background: rgba(11, 13, 32, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 22px 60px rgba(8, 11, 32, 0.45);
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            width: min(340px, 100%);
        }

        .metric-title {
            font-size: 0.8rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.58);
        }

        .metric-value {
            font-size: 2.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, var(--sky));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .metric-desc {
            color: var(--text-soft);
            font-size: 0.95rem;
        }

        .hero-scroll {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.58);
            font-size: 0.85rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .scroll-indicator {
            width: 1px;
            height: 48px;
            background: rgba(255, 255, 255, 0.18);
            position: relative;
            overflow: hidden;
        }

        .scroll-indicator::after {
            content: '';
            position: absolute;
            top: -20px;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.5));
            animation: scrollHint 2.4s ease infinite;
        }

        @keyframes scrollHint {
            0% { transform: translateY(0); opacity: 0; }
            30% { opacity: 1; }
            60% { transform: translateY(48px); opacity: 0; }
            100% { opacity: 0; }
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2.4rem;
        }

        .section-heading h2 {
            font-size: clamp(2.2rem, 4vw, 3.1rem);
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .section-heading p {
            max-width: 420px;
            color: var(--text-soft);
            font-size: 1rem;
        }

        .live-section {
            display: grid;
            gap: 3rem;
            grid-template-columns: repeat(12, 1fr);
            align-items: center;
        }

        .live-copy {
            grid-column: 1 / span 5;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .live-copy span {
            font-size: 0.85rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.58);
        }

        .live-copy h3 {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700;
            line-height: 1.1;
        }

        .live-copy p {
            color: var(--text-soft);
            font-size: 1rem;
        }

        .live-feed {
            grid-column: 6 / span 7;
            position: relative;
            border-radius: var(--radius-lg);
            padding: 1.8rem;
            background: rgba(11, 13, 32, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            box-shadow: 0 40px 100px rgba(8, 11, 32, 0.55);
        }

        .live-feed::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), transparent);
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        .live-feed:hover::before {
            opacity: 1;
        }

        .live-status {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1.4rem;
            border-radius: 999px;
            background: rgba(17, 20, 45, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.14);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-size: 0.78rem;
            margin-bottom: 1rem;
        }

        .live-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 14px rgba(34, 197, 94, 0.8);
            animation: pulseDot 1.6s ease infinite;
        }

        .feed-frame {
            position: relative;
            aspect-ratio: 16 / 9;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(2, 6, 20, 0.9);
        }

        .feed-frame iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: transparent;
        }

        .feed-note {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.65);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.6rem;
        }

        .feature-card {
            padding: 1.8rem;
            border-radius: var(--radius-md);
            background: rgba(11, 13, 32, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(18px);
            box-shadow: 0 16px 40px rgba(8, 11, 32, 0.4);
            display: grid;
            gap: 1rem;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            font-size: 1.8rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
        }

        .feature-card p {
            font-size: 0.98rem;
            color: var(--text-soft);
        }

        .timeline {
            position: relative;
            padding-left: 2.8rem;
            display: grid;
            gap: 2.5rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 1px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), transparent);
        }

        .timeline-step {
            position: relative;
            padding: 1.8rem 2rem;
            border-radius: var(--radius-md);
            background: rgba(11, 13, 32, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 40px rgba(8, 11, 32, 0.35);
        }

        .timeline-step::before {
            content: attr(data-step);
            position: absolute;
            left: -2.8rem;
            top: 1.8rem;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 50%;
            background: rgba(9, 12, 32, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            font-weight: 600;
            letter-spacing: 0.08em;
            font-size: 0.85rem;
        }

        .timeline-step h4 {
            font-size: 1.25rem;
            margin-bottom: 0.6rem;
        }

        .timeline-step p {
            color: var(--text-soft);
            font-size: 0.98rem;
        }

        .equipment-section {
            display: grid;
            gap: 3rem;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.6rem;
        }

        .equipment-card {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: rgba(10, 12, 32, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
            box-shadow: 0 24px 60px rgba(7, 9, 25, 0.45);
            display: grid;
            grid-template-rows: 220px auto;
            transition: transform 0.45s ease, box-shadow 0.45s ease;
        }

        .equipment-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 40px 90px rgba(99, 102, 241, 0.28);
        }

        .equipment-media {
            position: relative;
            overflow: hidden;
        }

        .equipment-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }

        .equipment-card:hover .equipment-media img {
            transform: scale(1.05);
        }

        .equipment-info {
            padding: 1.5rem;
            display: grid;
            gap: 0.85rem;
        }

        .equipment-meta {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.85rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.58);
        }

        .equipment-name {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .equipment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .equipment-details span {
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            letter-spacing: 0.06em;
        }

        .equipment-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .cta-section {
            margin: 5rem 0 6rem;
            padding: clamp(2rem, 6vw, 4rem);
            border-radius: var(--radius-lg);
            background: rgba(9, 10, 28, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            box-shadow: 0 40px 120px rgba(7, 9, 25, 0.6);
            display: grid;
            gap: 1.6rem;
            justify-items: start;
        }

        .cta-section h3 {
            font-size: clamp(2.2rem, 4vw, 2.8rem);
            font-weight: 700;
            line-height: 1.1;
        }

        .cta-section p {
            max-width: 520px;
            color: var(--text-soft);
        }

        footer {
            padding: 3rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            align-items: center;
            font-size: 0.85rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.55);
        }

        footer nav {
            position: static;
            background: none;
            backdrop-filter: none;
            box-shadow: none;
            padding: 0;
        }

        footer nav a {
            font-size: 0.82rem;
            margin: 0 0.8rem;
            letter-spacing: 0.14em;
        }

        footer nav a::after {
            display: none;
        }

        /* ANIMATION UTILS */
        [data-animate] {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        [data-animate].is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        [data-animate="fade-left"] {
            transform: translateX(-50px);
        }

        [data-animate="fade-left"].is-visible {
            transform: translateX(0);
        }

        [data-animate="fade-right"] {
            transform: translateX(50px);
        }

        [data-animate="fade-right"].is-visible {
            transform: translateX(0);
        }

        [data-animate="zoom-in"] {
            transform: scale(0.92);
        }

        [data-animate="zoom-in"].is-visible {
            transform: scale(1);
        }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(3, 4, 12, 0.92);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 200;
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal {
            width: min(520px, 90vw);
            background: rgba(7, 8, 24, 0.9);
            border-radius: var(--radius-lg);
            padding: clamp(2rem, 5vw, 3rem);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            box-shadow: 0 40px 120px rgba(5, 7, 20, 0.65);
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 1.4rem;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .modal-close:hover {
            transform: scale(1.06);
        }

        .modal h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
        }

        .modal p {
            color: var(--text-soft);
            margin-bottom: 1.6rem;
            font-size: 0.98rem;
        }

        .form-grid {
            display: grid;
            gap: 1rem;
        }

        label {
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.68);
        }

        input, select {
            width: 100%;
            padding: 0.9rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(10, 12, 32, 0.75);
            color: var(--text);
            font-size: 1rem;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .submit-btn {
            margin-top: 0.6rem;
            padding: 0.95rem 2.2rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            box-shadow: 0 24px 52px rgba(99, 102, 241, 0.35);
        }

        .submit-btn:hover {
            transform: translateY(-4px);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            transform: none;
            cursor: not-allowed;
            box-shadow: none;
        }

        .toast {
            position: fixed;
            top: 1.6rem;
            left: 50%;
            transform: translateX(-50%) translateY(-80px);
            padding: 0.85rem 1.6rem;
            border-radius: 999px;
            background: rgba(9, 10, 28, 0.9);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            z-index: 300;
            opacity: 0;
            transition: transform 0.35s ease, opacity 0.35s ease;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* RESPONSIVE */
        @media (max-width: 1080px) {
            nav, nav.scrolled {
                padding: 1rem 1.6rem;
            }

            .nav-links {
                display: none;
            }

            .hero {
                grid-template-columns: 1fr;
                gap: 3rem;
                margin-top: 4rem;
            }

            .hero-content {
                grid-column: 1 / -1;
            }

            .hero-visual {
                grid-column: 1 / -1;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }

            .live-section {
                grid-template-columns: 1fr;
            }

            .live-copy, .live-feed {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 7rem 1.5rem 4rem;
            }

            section {
                margin-bottom: 4rem;
            }

            .section-heading {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .hero-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-primary, .btn-ghost {
                width: 100%;
                justify-content: center;
            }

            .timeline {
                padding-left: 1.6rem;
            }

            .timeline-step::before {
                left: -2.2rem;
            }

            .equipment-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }

            .cta-section {
                justify-items: stretch;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body>
    <div class="bg-image"></div>
    <div class="bg-overlay"></div>
    <div id="stars"></div>

    <nav id="mainNav">
        <div class="nav-container">
            <div class="brand" data-animate="fade-left">STELLAR OBSERVATORY</div>
            <ul class="nav-links">
                <li><a href="#home">Accueil</a></li>
                <li><a href="#experience">Expérience</a></li>
                <li><a href="#equipments">Équipements</a></li>
                <li><a href="#rejoindre">Accès Anticipé</a></li>
            </ul>
            <button class="btn-nav" onclick="openWaitingList()">Waiting List</button>
        </div>
    </nav>

    <main>
        <section class="hero" id="home">
            <div class="hero-content" data-animate="fade-left">
                <div class="hero-badge">Nouvelle ère de l'observation</div>
                <h1 class="hero-title">Le cosmos, en direct, à portée de main.</h1>
                <p class="hero-text">
                    Stellar ouvre les portes des observatoires professionnels au grand public. Orientez les télescopes,
                    capturez des images signatures et partagez-les avec votre communauté sans quitter votre salon.
                </p>
                <div class="hero-actions">
                    <button class="btn-primary" onclick="openWaitingList()">Réserver mon accès</button>
                    <a href="#experience" class="btn-ghost">Découvrir l'expérience</a>
                </div>
                <div class="hero-scroll">
                    <div class="scroll-indicator"></div>
                    Faites défiler
                </div>
            </div>

            <div class="hero-visual" data-animate="fade-right">
                <div class="metric-card">
                    <span class="metric-title">Observatoires partenaires</span>
                    <span class="metric-value">12</span>
                    <span class="metric-desc">Implantés sur trois continents pour suivre le ciel sans interruption.</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Sessions immersives</span>
                    <span class="metric-value">2.3K</span>
                    <span class="metric-desc">Utilisateurs déjà conquis lors de nos sessions pilotes.</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Qualité d'image</span>
                    <span class="metric-value">8K</span>
                    <span class="metric-desc">Caméras scientifiques refroidies pour des détails stellaires époustouflants.</span>
                </div>
            </div>
        </section>

        <section id="experience" data-animate="fade-up">
            <div class="section-heading">
                <h2>Vivez le ciel, comme si vous y étiez</h2>
                <p>
                    De la mise au point au suivi d'objet, Stellar vous accompagne pas à pas. Les flux en direct, la réalité
                    augmentée et les recommandations scientifiques transforment votre expérience d'observation.
                </p>
            </div>

            <div class="live-section">
                <div class="live-copy" data-animate="fade-left">
                    <span>Flux en direct</span>
                    <h3>Contrôlez, observez, apprenez</h3>
                    <p>
                        Accédez en un clic à la caméra embarquée sur notre télescope de démonstration. Un aperçu de ce que vous
                        vivrez très bientôt directement depuis votre interface Stellar.
                    </p>
                    <p>
                        Activez le plein écran pour une immersion totale. Si le flux reste grisé, votre navigateur bloque peut-être
                        le contenu mixte : autorisez-le ou ouvrez le flux dans un onglet dédié.
                    </p>
                    <a class="btn-ghost" href="http://185.228.120.120:23003/public.php" target="_blank" rel="noreferrer">
                        Ouvrir le flux dans un nouvel onglet
                    </a>
                </div>

                <div class="live-feed" data-animate="fade-right">
                    <div class="live-status">
                        <span class="live-dot"></span>
                        Flux direct
                    </div>
                    <div class="feed-frame">
                        <iframe
                            src="http://185.228.120.120:23003/public.php"
                            title="Flux en direct du télescope Stellar"
                            allowfullscreen
                            allow="autoplay; fullscreen; picture-in-picture"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                        ></iframe>
                    </div>
                    <div class="feed-note">
                        <span>⚠️</span>
                        Autorisez le contenu non sécurisé si nécessaire pour afficher l'image en direct.
                    </div>
                </div>
            </div>
        </section>

        <section data-animate="fade-up">
            <div class="section-heading">
                <h2>Une plateforme taillée pour les explorateurs</h2>
                <p>
                    Chaque fonctionnalité a été pensée avec nos astronomes partenaires pour vous offrir un pilotage de classe professionnelle en quelques clics.
                </p>
            </div>

            <div class="feature-grid">
                <article class="feature-card" data-animate="zoom-in">
                    <div class="feature-icon">🎯</div>
                    <h3>Guidage assisté par IA</h3>
                    <p>Identifiez les objets les plus spectaculaires visibles depuis votre observatoire et centrez-les automatiquement.</p>
                </article>

                <article class="feature-card" data-animate="zoom-in">
                    <div class="feature-icon">🛰️</div>
                    <h3>Remote Control Premium</h3>
                    <p>Accédez aux commandes fines des télescopes : pointage, suivi, filtres, capture, stack en live.</p>
                </article>

                <article class="feature-card" data-animate="zoom-in">
                    <div class="feature-icon">📡</div>
                    <h3>Visuel + données</h3>
                    <p>Combinez le flux vidéo à haute fréquence avec les métriques physiques pour comprendre ce que vous observez.</p>
                </article>

                <article class="feature-card" data-animate="zoom-in">
                    <div class="feature-icon">📚</div>
                    <h3>Ateliers immersifs</h3>
                    <p>Rejoignez des sessions live animées par nos astrophysiciens pour apprendre à maîtriser l'instrumentation.</p>
                </article>
            </div>
        </section>

        <section data-animate="fade-up">
            <div class="section-heading">
                <h2>Votre aventure en trois temps</h2>
            </div>

            <div class="timeline">
                <div class="timeline-step" data-step="01" data-animate="fade-left">
                    <h4>Inscription & onboarding</h4>
                    <p>Choisissez votre profil, vos objectifs d'observation et votre créneau de prise en main.</p>
                </div>
                <div class="timeline-step" data-step="02" data-animate="fade-left">
                    <h4>Session guidée</h4>
                    <p>Un expert Stellar vous accompagne pour calibrer l'instrument et maîtriser vos premiers clichés.</p>
                </div>
                <div class="timeline-step" data-step="03" data-animate="fade-left">
                    <h4>Autonomie totale</h4>
                    <p>Prenez les commandes, explorez nos scénarios d'observation et partagez vos découvertes.</p>
                </div>
            </div>
        </section>

        <section id="equipments" data-animate="fade-up" class="equipment-section">
            <div class="section-heading">
                <h2>Un parc d'instruments d'exception</h2>
                <p>
                    Des optiques à large ouverture, des montures ultra-stables, des capteurs refroidis. Une sélection pointue
                    validée par nos astronomes partenaires.
                </p>
            </div>

            <div class="equipment-grid">
                @php($list = isset($featuredEquipment) && count($featuredEquipment) ? $featuredEquipment : (isset($equipment) ? $equipment : []))

                @forelse($list as $eq)
                    <article class="equipment-card" data-animate="zoom-in">
                        <div class="equipment-media">
                            <img src="{{ $eq->getMainImage() }}" alt="{{ $eq->name }}" loading="lazy">
                        </div>
                        <div class="equipment-info">
                            <div class="equipment-meta">
                                <span>{{ $eq->location ?: $eq->getTypeLabel() }}</span>
                                <span>•</span>
                                <span>{{ strtoupper($eq->statusLabel) }}</span>
                            </div>
                            <div class="equipment-name">{{ $eq->name }}</div>
                            <div class="equipment-details">
                                <span>🔭 {{ $eq->getTypeLabel() }}</span>
                                @if($eq->specifications && isset($eq->specifications['aperture']))
                                    <span>🌀 {{ $eq->specifications['aperture'] }}</span>
                                @endif
                                @if($eq->specifications && isset($eq->specifications['focal_length']))
                                    <span>📏 {{ $eq->specifications['focal_length'] }}</span>
                                @endif
                            </div>
                            <div class="equipment-footer">
                                <span>{{ $eq->price_per_hour_credits }} crédits / h</span>
                                <span>Réservable bientôt</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="equipment-card" data-animate="zoom-in">
                        <div class="equipment-media">
                            <img src="https://images.unsplash.com/photo-1444703686981-a3abbc4d4fe3?auto=format&fit=crop&w=800&q=80" alt="Télescope" loading="lazy">
                        </div>
                        <div class="equipment-info">
                            <div class="equipment-meta">
                                <span>Désert d'Atacama</span>
                                <span>•</span>
                                <span>Disponible</span>
                            </div>
                            <div class="equipment-name">Télescope Ritchey-Chrétien 20"</div>
                            <div class="equipment-details">
                                <span>🔭 Optique RC</span>
                                <span>🌀 500 mm</span>
                                <span>📏 f/8</span>
                            </div>
                            <div class="equipment-footer">
                                <span>65 crédits / h</span>
                                <span>Réservable bientôt</span>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>
        </section>

        <section id="rejoindre" class="cta-section" data-animate="fade-up">
            <h3>Rejoignez la liste d'attente et vivez les premières observations grand public</h3>
            <p>
                Places limitées. Les membres de la waiting list recevront un accès VIP, une tarification préférentielle et des
                invitations à nos soirées d'observation privées.
            </p>
            <button class="btn-primary" onclick="openWaitingList()">Je m'inscris</button>
        </section>
    </main>

    <footer>
        <div>© {{ date('Y') }} Stellar. Tous droits réservés.</div>
        <nav>
            <a href="#home">Accueil</a>
            <a href="#experience">Expérience</a>
            <a href="#equipments">Équipements</a>
            <a href="#rejoindre">Waiting List</a>
        </nav>
        <div>Explorons l'univers ensemble.</div>
    </footer>

    <!-- WAITING LIST MODAL -->
    <div class="modal-overlay" id="waitingListModal">
        <div class="modal">
            <button class="modal-close" onclick="closeWaitingList()">×</button>
            <h2>Réservez votre accès privilégié</h2>
            <p>
                Inscrivez-vous pour être averti(e) dès l'ouverture des premières sessions. Vous recevrez nos actualités, des
                invitations exclusives et des offres réservées aux premiers arrivés.
            </p>
            <form id="waitingListForm" class="form-grid">
                <div>
                    <label for="firstName">Prénom</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Votre prénom" required>
                </div>
                <div>
                    <label for="lastName">Nom</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Votre nom" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="vous@example.com" required>
                </div>
                <div>
                    <label for="interest">Votre profil</label>
                    <select id="interest" name="interest" required>
                        <option value="">Sélectionnez votre niveau</option>
                        <option value="debutant">Débutant curieux</option>
                        <option value="amateur">Amateur passionné</option>
                        <option value="avance">Utilisateur avancé</option>
                        <option value="professionnel">Professionnel</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn">Valider mon inscription</button>
            </form>
        </div>
    </div>

    <div id="toastContainer"></div>

    <script>
        const starsContainer = document.getElementById('stars');

        function createStars() {
            const total = window.innerWidth < 768 ? 60 : 110;
            starsContainer.innerHTML = '';
            for (let i = 0; i < total; i++) {
                const star = document.createElement('span');
                const size = Math.random() * 2 + 0.6;
                star.style.position = 'absolute';
                star.style.width = size + 'px';
                star.style.height = size + 'px';
                star.style.borderRadius = '50%';
                star.style.background = `rgba(255,255,255,${Math.random() * 0.7 + 0.2})`;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.boxShadow = `0 0 ${Math.random() * 8 + 3}px rgba(255,255,255,0.7)`;
                star.style.animation = `twinkle ${8 + Math.random() * 6}s ease-in-out infinite`;
                star.style.animationDelay = (-Math.random() * 8) + 's';
                starsContainer.appendChild(star);
            }
        }

        const styles = document.createElement('style');
        styles.innerHTML = `
            @keyframes twinkle {
                0%, 100% { opacity: 0.4; transform: scale(1); }
                50% { opacity: 1; transform: scale(1.6); }
            }
        `;
        document.head.appendChild(styles);

        createStars();
        window.addEventListener('resize', () => {
            clearTimeout(window.__starTimer);
            window.__starTimer = setTimeout(createStars, 400);
        });

        const observerOptions = {
            threshold: 0.25,
            rootMargin: '0px 0px -80px 0px'
        };

        const animateObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    animateObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-animate]').forEach(el => {
            animateObserver.observe(el);
        });

        const mainNav = document.getElementById('mainNav');
        const heroSection = document.getElementById('home');

        function handleNav() {
            const offset = heroSection ? heroSection.offsetHeight - 120 : 120;
            if (window.scrollY > offset) {
                mainNav.classList.add('scrolled');
            } else {
                mainNav.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', handleNav);

        function openWaitingList() {
            document.getElementById('waitingListModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeWaitingList() {
            document.getElementById('waitingListModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.getElementById('waitingListModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeWaitingList();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeWaitingList();
            }
        });

        function showToast(message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = message;
            container.appendChild(toast);

            requestAnimationFrame(() => toast.classList.add('show'));

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 350);
            }, 3800);
        }

        document.getElementById('waitingListForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Inscription en cours...';

            const formData = new FormData(e.target);
            const payload = {
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                interest: formData.get('interest')
            };

            try {
                const response = await fetch('/waiting-list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok) {
                    showToast('✅ Bienvenue dans la constellation Stellar !');
                    e.target.reset();
                    setTimeout(closeWaitingList, 1200);
                } else if (response.status === 409) {
                    showToast(`ℹ️ ${result.message}`);
                    setTimeout(closeWaitingList, 1800);
                } else if (response.status === 422) {
                    const errors = result.errors;
                    if (errors && errors.email) {
                        showToast(`❌ ${errors.email[0]}`);
                    } else {
                        showToast('❌ Vérifiez vos informations.');
                    }
                } else {
                    showToast(`❌ ${result.message || 'Une erreur est survenue.'}`);
                }
            } catch (error) {
                console.error(error);
                showToast('❌ Impossible de contacter le serveur.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Valider mon inscription';
            }
        });
    </script>
</body>
</html>
