<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Keijora POS - Sistem Kasir Modern untuk F&B</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --keijora-navy: #001356;
            --keijora-navy-soft: #1b2b6b;
            --keijora-ink: #15212a;
            --keijora-muted: #56616d;
            --keijora-line: #d7dde8;
            --keijora-soft: #f6faff;
            --keijora-mint: #6ffbbe;
            --keijora-blue: #dce9ff;
            --hero-nav-height: 64px;
            --text-xs: 0.75rem;
            --text-sm: 0.875rem;
            --text-base: 1rem;
            --text-lg: 1.125rem;
            --text-xl: 1.25rem;
            --text-2xl: clamp(1.75rem, 3vw, 2.6rem);
            --text-3xl: clamp(2.2rem, 5vw, 3.95rem);
            --radius-soft: 24px;
            --radius-surface: 28px;
            --radius-pill: 999px;
            --shadow-soft: 0 14px 36px rgba(0, 19, 86, .08);
            --shadow-surface: 0 20px 50px rgba(0, 19, 86, .10);
        }

        * {
            box-sizing: border-box;
        }

        html {
            -webkit-text-size-adjust: 100%;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: var(--text-base);
            line-height: 1.65;
            color: var(--keijora-ink);
            background: #ffffff;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .money {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0;
        }

        .page-shell {
            position: relative;
            overflow: visible;
            background: #ffffff;
        }

        .page-shell::before {
            content: none;
        }

        .site-container {
            width: min(1120px, calc(100% - 32px));
            margin-inline: auto;
        }

        .hero-nav {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 50;
            border-bottom: 0;
            background: transparent;
            box-shadow: none;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            transition: background .25s ease, box-shadow .25s ease, border-color .25s ease, backdrop-filter .25s ease;
        }

        .hero-nav::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 28px;
            background: linear-gradient(180deg, rgba(6, 18, 48, .22), rgba(6, 18, 48, .08) 35%, rgba(6, 18, 48, 0));
            opacity: .72;
            pointer-events: none;
            transform: translateY(-1px);
        }

        .hero-nav.is-scrolled {
            border-bottom: 1px solid rgba(215, 221, 232, .80);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .72), rgba(255, 255, 255, .58)),
                rgba(255, 255, 255, .68);
            box-shadow:
                0 16px 36px rgba(0, 19, 86, .12),
                inset 0 1px 0 rgba(255, 255, 255, .55);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .nav-inner {
            display: flex;
            min-height: var(--hero-nav-height);
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand-lockup {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: 7px;
            color: inherit;
            text-decoration: none;
        }

        .brand-bird {
            width: 26px;
            height: 26px;
            object-fit: contain;
        }

        .brand-word {
            width: 84px;
            height: auto;
            object-fit: contain;
        }

        .brand-sub {
            margin-top: 1px;
            color: #6f737b;
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: .18em;
            line-height: 1;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #3f4852;
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .nav-links a,
        .footer-link {
            color: inherit;
            text-decoration: none;
            transition: color .2s ease, opacity .2s ease;
        }

        .nav-links a:hover,
        .footer-link:hover {
            color: var(--keijora-navy);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .btn {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 12px;
            padding: 0 14px;
            border: 1px solid transparent;
            font-size: var(--text-sm);
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            transition: transform .24s ease, box-shadow .24s ease, background .24s ease, border-color .24s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            color: #fff;
            background: var(--keijora-navy);
            box-shadow: 0 12px 28px rgba(0, 19, 86, .18);
        }

        .btn-primary:hover {
            background: var(--keijora-navy-soft);
            box-shadow: 0 16px 34px rgba(0, 19, 86, .22);
        }

        .btn-secondary {
            color: var(--keijora-navy);
            border-color: rgba(0, 19, 86, .18);
            background: rgba(255, 255, 255, .58);
        }

        .btn-secondary:hover {
            border-color: rgba(0, 19, 86, .3);
            background: #ffffff;
        }

        .hero {
            position: relative;
            z-index: 1;
            padding: calc(var(--hero-nav-height) + 58px) 0 0;
            padding-bottom: 28px;
            text-align: center;
            overflow: hidden;
            background: #ffffff; /* fallback sebelum video load */
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

       .hero-overlay {
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .25) 0%, rgba(255, 255, 255, .45) 55%, rgba(255, 255, 255, .75) 100%),
        rgba(0, 0, 0, .35);
        }
        
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 50% 18%, rgba(255, 255, 255, .92), transparent 24rem),
                radial-gradient(circle at 16% 30%, rgba(111, 251, 190, .28), transparent 20rem),
                radial-gradient(circle at 82% 34%, rgba(220, 233, 255, .72), transparent 22rem);
            z-index: -1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            border: 1px solid rgba(0, 19, 86, .14);
            border-radius: 999px;
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 12px 24px rgba(21, 33, 42, .08);
            padding: 8px 14px;
            color: var(--keijora-navy);
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            backdrop-filter: blur(16px);
        }

        .eyebrow-dot {
            width: 8px;
            height: 8px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: var(--keijora-mint);
            box-shadow: 0 0 0 6px rgba(111, 251, 190, .2);
        }

        .hero-title {
            max-width: 820px;
            margin: 22px auto 0;
            color: var(--keijora-navy);
            font-size: var(--text-3xl);
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
        }

        .hero-copy {
            max-width: 710px;
            margin: 20px auto 0;
            color: #34404c;
            font-size: var(--text-lg);
            line-height: 1.7;
        }

        .hero-copy-mobile {
            display: none;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .hero-stage {
            position: relative;
            max-width: 900px;
            margin: 34px auto 0;
            min-height: 410px;
        }

        .hero-stage::before {
            content: "";
            position: absolute;
            inset: 26px 8% 20px;
            border-radius: 42px;
            background: rgba(255, 255, 255, .45);
            box-shadow: 0 35px 90px rgba(0, 19, 86, .14);
            filter: blur(1px);
        }

        .hero-card {
            position: relative;
            z-index: 2;
            width: min(710px, 86vw);
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid rgba(180, 190, 208, .9);
            border-radius: var(--radius-surface);
            background: rgba(255, 255, 255, .92);
            box-shadow: var(--shadow-surface);
            backdrop-filter: blur(18px);
            text-align: left;
            animation: floatMain 8s ease-in-out infinite;
        }

        .hero-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--keijora-line);
            padding: 16px 18px;
        }

        .window-dots {
            display: flex;
            gap: 6px;
        }

        .window-dots span {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #d8dde7;
        }

        .hero-dashboard {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 16px;
            padding: 18px;
            background:
                linear-gradient(180deg, rgba(246, 250, 255, .86), rgba(255, 255, 255, .96)),
                radial-gradient(circle at top right, rgba(111, 251, 190, .18), transparent 16rem);
        }

        .mock-sidebar,
        .mock-panel,
        .mock-phone,
        .mock-receipt,
        .metric-pill,
        .feature-tile,
        .workflow-card,
        .story-card {
            border: 1px solid var(--keijora-line);
            background: rgba(255, 255, 255, .88);
            box-shadow: var(--shadow-soft);
        }

        .mock-sidebar {
            border-radius: 20px;
            padding: 14px;
        }

        .mock-menu {
            display: flex;
            align-items: center;
            gap: 9px;
            border-radius: 14px;
            padding: 10px;
            color: #526071;
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .mock-menu.active {
            color: #fff;
            background: var(--keijora-navy);
        }

        .mock-content {
            min-width: 0;
        }

        .mock-search {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--keijora-line);
            border-radius: 999px;
            background: #fff;
            padding: 11px 14px;
            color: #7a8390;
            font-size: var(--text-sm);
            font-weight: 600;
        }

        .mock-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .mock-product {
            border: 1px solid var(--keijora-line);
            border-radius: 18px;
            background: #fff;
            padding: 10px;
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .mock-product:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(0, 19, 86, .1);
        }

        .mock-product-image {
            display: flex;
            height: 74px;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #dfe6f0;
            color: var(--keijora-navy);
        }

        .mock-product-name {
            margin-top: 10px;
            color: var(--keijora-ink);
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .mock-product-price {
            margin-top: 4px;
            color: var(--keijora-navy);
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .mock-phone {
            position: absolute;
            z-index: 4;
            right: 8px;
            bottom: 22px;
            width: 176px;
            border-radius: 22px;
            padding: 10px;
            animation: floatSide 7s ease-in-out infinite;
        }

        .mock-phone-screen {
            min-height: 240px;
            border-radius: 18px;
            background: #f8fbff;
            padding: 12px;
        }

        .chat-row {
            margin-top: 8px;
            border-radius: 14px;
            padding: 9px;
            font-size: var(--text-xs);
            line-height: 1.5;
        }

        .chat-row.left {
            background: #e8eef8;
            color: #273543;
        }

        .chat-row.right {
            margin-left: 18px;
            background: #dbfff0;
            color: #005236;
        }

        .mock-receipt {
            position: absolute;
            z-index: 3;
            left: 2px;
            bottom: 6px;
            width: 190px;
            border-radius: 22px;
            padding: 16px;
            animation: floatSide 9s ease-in-out infinite reverse;
        }

        .receipt-line {
            height: 7px;
            border-radius: 999px;
            background: #e1e7f0;
        }

        .receipt-line.dark {
            background: var(--keijora-navy);
        }

        .trust-strip {
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(215, 221, 232, .8);
            border-bottom: 1px solid rgba(215, 221, 232, .8);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(14px);
        }

        .trust-inner {
            display: grid;
            grid-template-columns: 1.05fr 2fr;
            gap: 34px;
            align-items: center;
            min-height: 98px;
            padding: 0;
        }

        .trust-copy {
            display: flex;
            align-items: center;
            align-self: stretch;
            border-left: 1px solid var(--keijora-line);
            border-right: 1px solid var(--keijora-line);
            padding: 24px 26px;
            color: var(--keijora-ink);
            font-size: var(--text-lg);
            font-weight: 700;
            line-height: 1.55;
        }

        .logo-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 18px;
        }

        .logo-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            color: rgba(21, 33, 42, .48);
            font-size: var(--text-sm);
            font-weight: 700;
            filter: grayscale(1);
            opacity: .76;
        }

        .logo-pill .material-symbols-outlined {
            font-size: 24px;
        }

        .metric-pill {
            border-radius: 999px;
            padding: 10px 14px;
            color: #526071;
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .testimonial-band {
            border-bottom: 1px solid var(--keijora-line);
            background: rgba(255, 255, 255, .96);
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, .85fr);
            gap: 62px;
            align-items: stretch;
            min-height: 300px;
            padding: 54px 0;
        }

        .testimonial-quote {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .quote-text {
            min-height: 132px;
            color: var(--keijora-ink);
            font-size: var(--text-2xl);
            font-weight: 800;
            line-height: 1.16;
            letter-spacing: 0;
            transition: opacity .28s ease, transform .28s ease;
        }

        .quote-text.is-changing {
            opacity: 0;
            transform: translateY(8px);
        }

        .quote-source {
            margin-top: 22px;
            color: rgba(21, 33, 42, .45);
            font-size: var(--text-base);
            font-weight: 600;
            transition: opacity .28s ease, transform .28s ease;
        }

        .quote-source.is-changing {
            opacity: 0;
            transform: translateY(6px);
        }

        .testimonial-list {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .testimonial-item {
            position: relative;
            display: grid;
            grid-template-columns: 54px minmax(0, 1fr);
            gap: 14px;
            align-items: center;
            min-height: 86px;
            border-top: 1px solid var(--keijora-line);
            color: rgba(21, 33, 42, .38);
            cursor: pointer;
            padding: 16px 0;
            transition: color .25s ease, padding-left .25s ease;
        }

        .testimonial-item::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -1px;
            width: 0;
            height: 2px;
            background: var(--keijora-ink);
            transition: width .36s ease;
        }

        .testimonial-item:last-child {
            border-bottom: 1px solid var(--keijora-line);
        }

        .testimonial-item.is-active {
            color: var(--keijora-ink);
            padding-left: 2px;
        }

        .testimonial-item.is-active::after {
            width: 94%;
        }

        .testimonial-avatar {
            display: grid;
            width: 54px;
            height: 54px;
            place-items: center;
            border-radius: 16px;
            color: var(--keijora-navy);
            background:
                radial-gradient(circle at 30% 25%, rgba(111, 251, 190, .9), transparent 28px),
                #e9eff8;
            font-size: 17px;
            font-weight: 800;
            opacity: 0;
            transform: scale(.88);
            transition: opacity .25s ease, transform .25s ease;
        }

        .testimonial-item.is-active .testimonial-avatar {
            opacity: 1;
            transform: scale(1);
        }

        .testimonial-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: var(--text-xl);
            font-weight: 700;
            line-height: 1.12;
        }

        .testimonial-role {
            display: none;
            margin-top: 5px;
            overflow: hidden;
            color: rgba(21, 33, 42, .52);
            font-size: var(--text-sm);
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .testimonial-item.is-active .testimonial-role {
            display: block;
        }

        .section {
            padding: 74px 0;
            scroll-margin-top: calc(var(--hero-nav-height) + 20px);
        }

        .section-head {
            max-width: 680px;
            margin-bottom: 34px;
        }

        .section-kicker {
            margin: 0 0 12px;
            color: #005236;
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0;
            color: var(--keijora-ink);
            font-size: var(--text-2xl);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .section-copy {
            margin: 16px 0 0;
            color: var(--keijora-muted);
            font-size: var(--text-base);
            line-height: 1.75;
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(320px, .92fr) minmax(0, 1.08fr);
            gap: 0;
            align-items: stretch;
        }

        .feature-split {
            margin-top: 8px;
        }

        .feature-copy-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100%;
            border-right: 1px solid rgba(215, 221, 232, .9);
            padding: 42px 38px 42px 0;
        }

        .feature-actions {
            justify-content: flex-start;
            margin-top: 28px;
        }

        .feature-showcase {
            position: relative;
            min-height: 360px;
            overflow: hidden;
            border: 1px solid var(--keijora-line);
            border-radius: var(--radius-surface);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .82), rgba(255, 255, 255, .95)),
                url("{{ asset('images/hero-sea.jpg') }}") center / cover no-repeat;
            box-shadow: var(--shadow-surface);
        }

        .showcase-card {
            position: absolute;
            border: 1px solid var(--keijora-line);
            border-radius: 20px;
            background: rgba(255, 255, 255, .92);
            box-shadow: var(--shadow-soft);
            padding: 14px;
            backdrop-filter: blur(14px);
        }

        .showcase-main {
            left: 12%;
            top: 18%;
            width: 68%;
        }

        .showcase-side {
            right: 8%;
            bottom: 12%;
            width: 34%;
        }

        .mini-row {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            padding: 10px;
            background: #f4f7fb;
        }

        .mini-icon {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 10px;
            color: var(--keijora-navy);
            background: #dfe9ff;
        }

        .workflow-visual {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0;
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 30px;
            overflow: hidden;
            background: rgba(255, 255, 255, .88);
        }

        .workflow-card {
            display: flex;
            min-height: 228px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 0;
            border-right: 1px solid rgba(215, 221, 232, .9);
            border-bottom: 0;
            border-radius: 0;
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, .96));
            box-shadow: none;
            transition: background .24s ease, transform .24s ease;
        }

        .workflow-card:last-child {
            border-right: 0;
        }

        .workflow-card:hover {
            transform: translateY(-2px);
            background: #fff;
        }

        .workflow-icon {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 18px;
            color: #1a73e8;
            background: linear-gradient(180deg, rgba(220, 233, 255, .78), rgba(255, 255, 255, .95));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .88);
        }

        .workflow-icon .material-symbols-outlined {
            font-size: 1.65rem;
        }

        .workflow-title {
            margin: 18px 0 0;
            color: #1e293b;
            font-size: var(--text-xl);
            font-weight: 800;
            line-height: 1.15;
        }

        .workflow-text {
            margin: 8px 0 0;
            color: var(--keijora-muted);
            font-size: var(--text-sm);
            line-height: 1.7;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            min-height: 100%;
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 30px;
            overflow: hidden;
            background: rgba(255, 255, 255, .88);
        }

        .feature-tile {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 240px;
            border: 0;
            border-right: 1px solid rgba(215, 221, 232, .9);
            border-bottom: 1px solid rgba(215, 221, 232, .9);
            border-radius: 0;
            padding: 36px 28px;
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, .96));
            box-shadow: none;
            transition: background .24s ease, transform .24s ease;
        }

        .feature-tile:hover {
            transform: translateY(-2px);
            background: #fff;
        }

        .feature-grid-template .feature-tile:nth-child(2n) {
            border-right: 0;
        }

        .feature-grid-template .feature-tile:nth-last-child(-n+2) {
            border-bottom: 0;
        }

        .feature-icon {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 18px;
            color: #1a73e8;
            background: linear-gradient(180deg, rgba(220, 233, 255, .78), rgba(255, 255, 255, .95));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .88);
        }

        .feature-icon .material-symbols-outlined {
            font-size: 1.65rem;
        }

        .feature-title {
            margin: 18px 0 0;
            color: #1e293b;
            font-size: var(--text-xl);
            font-weight: 800;
            line-height: 1.15;
        }

        .feature-link {
            margin: 12px 0 0;
            color: #1a73e8;
            font-size: var(--text-sm);
            font-weight: 700;
            text-decoration: none;
        }

        .feature-link:hover {
            text-decoration: underline;
        }

        .story-layout {
            display: grid;
            gap: 28px;
        }

        .story-media {
            position: relative;
            overflow: hidden;
            min-height: 380px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .2), rgba(255, 255, 255, .28)),
                url("{{ asset('images/hero-sea.jpg') }}") center / cover no-repeat;
            box-shadow: var(--shadow-surface);
        }

        .story-media::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 22% 24%, rgba(255, 255, 255, .88), transparent 15%),
                radial-gradient(circle at 52% 42%, rgba(111, 251, 190, .18), transparent 18%),
                radial-gradient(circle at 78% 30%, rgba(220, 233, 255, .55), transparent 16%),
                linear-gradient(180deg, rgba(246, 250, 255, .20), rgba(246, 250, 255, .04));
            opacity: .92;
            pointer-events: none;
        }

        .story-pill {
            position: absolute;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 84px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .76);
            border: 1px solid rgba(255, 255, 255, .82);
            box-shadow: 0 12px 30px rgba(0, 19, 86, .10);
            backdrop-filter: blur(12px);
        }

        .story-pill .material-symbols-outlined {
            font-size: 2.1rem;
            color: #1a73e8;
        }

        .story-pill.one {
            left: 16%;
            top: 26%;
        }

        .story-pill.two {
            left: 42%;
            top: 22%;
        }

        .story-pill.three {
            left: 67%;
            top: 31%;
        }

        .story-pill.four {
            left: 28%;
            bottom: 18%;
        }

        .story-pill.five {
            left: 58%;
            bottom: 16%;
        }

        .story-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .story-item {
            position: relative;
            padding-top: 18px;
            border-top: 1px solid rgba(215, 221, 232, .9);
        }

        .story-item::before {
            content: "";
            position: absolute;
            top: -1px;
            left: 0;
            width: 86px;
            height: 2px;
            background: var(--keijora-navy);
        }

        .cta-band {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(215, 221, 232, .92);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(246, 250, 255, .98)),
                url("{{ asset('images/hero-sea.jpg') }}") center / cover no-repeat;
            padding: 56px 34px;
            color: var(--keijora-ink);
            text-align: left;
            box-shadow: var(--shadow-surface);
        }

        .cta-band::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 18% 12%, rgba(111, 251, 190, .20), transparent 18rem),
                radial-gradient(circle at 84% 16%, rgba(220, 233, 255, .55), transparent 18rem);
            pointer-events: none;
        }

        .cta-band-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .cta-kicker {
            margin: 0 0 12px;
            color: #005236;
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .cta-title {
            max-width: 720px;
            margin: 0;
            color: var(--keijora-navy);
            font-size: var(--text-2xl);
            font-weight: 800;
            line-height: 1.05;
        }

        .cta-copy {
            max-width: 640px;
            margin: 16px 0 0;
            color: var(--keijora-muted);
            font-size: var(--text-base);
            line-height: 1.75;
        }

        .cta-actions {
            justify-content: flex-start;
            margin-top: 24px;
        }

        .cta-actions .btn-secondary {
            background: rgba(255, 255, 255, .72);
        }

        .partner-strip {
            border-top: 1px solid rgba(215, 221, 232, .8);
            border-bottom: 1px solid rgba(215, 221, 232, .8);
            background: rgba(255, 255, 255, .94);
        }

        .partner-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 74px;
        }

        .partner-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--keijora-ink);
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .partner-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: #f0f4fb;
            color: var(--keijora-navy);
        }

        .partner-text {
            color: #4f5a66;
            font-size: var(--text-sm);
            font-weight: 600;
        }

        .footer {
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(215, 221, 232, .8);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(250, 252, 255, 1)),
                #fff;
            padding-bottom: 30px;
        }

        .footer::after {
            content: "Keijora";
            position: absolute;
            left: 50%;
            bottom: 26px;
            transform: translateX(-50%);
            color: rgba(0, 19, 86, .05);
            font-size: min(14vw, 136px);
            font-weight: 800;
            line-height: .8;
            pointer-events: none;
            white-space: nowrap;
        }

        .footer-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1.35fr .95fr;
            gap: 34px;
            align-items: start;
            padding: 44px 0 24px;
        }

        .footer-brand-block {
            display: grid;
            gap: 18px;
        }

        .footer-social {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .social-icon {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 999px;
            border: 1px solid rgba(215, 221, 232, .9);
            background: #fff;
            color: var(--keijora-navy);
        }

        .footer-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-tab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 999px;
            padding: 0 12px;
            color: #5b6571;
            background: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .footer-tab.is-active {
            border-color: rgba(0, 19, 86, .14);
            box-shadow: 0 10px 24px rgba(0, 19, 86, .06);
            color: var(--keijora-navy);
        }

        .office-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .office-card {
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 18px;
            background: rgba(255, 255, 255, .86);
            padding: 14px;
            box-shadow: 0 10px 22px rgba(0, 19, 86, .05);
        }

        .office-label {
            margin: 0 0 8px;
            color: #7a8390;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .office-title {
            margin: 0;
            color: var(--keijora-navy);
            font-size: 14px;
            font-weight: 800;
        }

        .office-copy {
            margin: 6px 0 0;
            color: var(--keijora-muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .footer-aside {
            display: grid;
            gap: 22px;
        }

        .download-block,
        .footer-links-block {
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 22px;
            background: rgba(255, 255, 255, .9);
            padding: 18px;
            box-shadow: 0 10px 26px rgba(0, 19, 86, .05);
        }

        .download-title,
        .footer-links-title {
            margin: 0 0 10px;
            color: var(--keijora-ink);
            font-size: 13px;
            font-weight: 800;
        }

        .store-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .store-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            border: 1px solid rgba(215, 221, 232, .9);
            border-radius: 10px;
            padding: 0 12px;
            background: #111827;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .footer-links-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .footer-links-list {
            display: grid;
            gap: 8px;
            margin: 0;
        }

        .footer-links-list a {
            color: #50606f;
            font-size: 13px;
            text-decoration: none;
        }

        .footer-links-list a:hover {
            color: var(--keijora-navy);
        }

        .footer-copy {
            position: relative;
            z-index: 1;
            margin-top: 8px;
            color: #7a8390;
            font-size: var(--text-xs);
            text-align: center;
        }

        .footer-divider {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            border-top: 1px solid rgba(215, 221, 232, .8);
        }

        .footer-copy-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 14px;
        }

        .reveal {
            opacity: 0;
            animation: fadeUp .78s ease forwards;
        }

        .delay-1 {
            animation-delay: .08s;
        }

        .delay-2 {
            animation-delay: .16s;
        }

        .delay-3 {
            animation-delay: .24s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatMain {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes floatSide {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-12px) rotate(.8deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }

        @media (max-width: 1024px) {
            .site-container {
                width: min(100% - 28px, 920px);
            }

            .hero-stage {
                min-height: 520px;
            }

            .hero-card {
                width: min(720px, 100%);
            }

            .mock-phone {
                right: 8%;
                bottom: 18px;
            }

            .mock-receipt {
                left: 7%;
                bottom: 28px;
            }

            .split-grid,
            .testimonial-grid,
            .trust-inner {
                grid-template-columns: 1fr;
            }

            .trust-inner {
                gap: 0;
                padding: 18px 0;
            }

            .trust-copy {
                border: 0;
                padding: 0 0 18px;
            }

            .logo-row {
                justify-content: flex-start;
            }

            .testimonial-grid {
                gap: 20px;
                min-height: auto;
                padding: 38px 0;
            }

            .workflow-visual {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .workflow-card:nth-child(2n) {
                border-right: 0;
            }

            .feature-grid {
                border-radius: 24px;
            }

            .feature-tile {
                min-height: 210px;
            }

            .feature-tile:nth-child(2n) {
                border-right: 0;
            }

            .feature-tile:nth-last-child(-n+2) {
                border-bottom: 0;
            }

            .story-layout {
                gap: 20px;
            }

            .story-media {
                min-height: 280px;
                border-radius: 24px;
            }

            .story-pill {
                width: 68px;
                height: 68px;
            }

            .story-pill.one {
                left: 10%;
                top: 24%;
            }

            .story-pill.two {
                left: 38%;
                top: 20%;
            }

            .story-pill.three {
                left: 66%;
                top: 28%;
            }

            .story-pill.four {
                left: 20%;
                bottom: 14%;
            }

            .story-pill.five {
                left: 56%;
                bottom: 12%;
            }
        }

        @media (max-width: 760px) {
            .site-container {
                width: min(100% - 24px, 640px);
            }

            .nav-inner {
                min-height: 60px;
            }

            .brand-word {
                width: 82px;
            }

            .brand-sub {
                font-size: var(--text-xs);
                letter-spacing: .2em;
            }

            .nav-links,
            .nav-login {
                display: none;
            }

            .btn {
                min-height: 40px;
                border-radius: 12px;
                padding: 0 14px;
                font-size: var(--text-sm);
            }

            .hero {
                text-align: left;
                padding-top: calc(var(--hero-nav-height) + 50px);
                padding-bottom: 100px;
            }

            .hero-title {
            margin-left: 0;
            margin-right: 0;
            }

            .hero-overlay {
            background:
            linear-gradient(180deg, rgba(255, 255, 255, .10) 0%, rgba(255, 255, 255, .25) 55%, rgba(255, 255, 255, .50) 100%),
            rgba(0, 0, 0, .30);
         }

            .hero-copy {
                margin-left: 0;
                margin-right: 0;
            }

            .hero-copy-desktop {
                display: none;
            }

            .hero-copy-mobile {
                display: inline;
            }

            .hero-actions {
                justify-content: flex-start;
            }

            .hero-actions .btn {
                width: 100%;
                min-height: 50px;
            }

            .split-grid {
                gap: 18px;
            }

            .feature-copy-panel {
                border-right: 0;
                padding: 0;
            }

            .hero-stage {
                display: none;
                flex-direction: column;
                align-items: center;
                gap: 14px;
                min-height: 0;
                margin-top: 10px;
                padding-bottom: 20px;

            }

            .hero-stage::before {
                inset: 14px 0 10px;
                border-radius: 24px;
            }

            .hero-dashboard {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .mock-sidebar {
                display: none;
            }

            .mock-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .mock-product-image {
                height: 58px;
            }

            .mock-product:nth-child(n+5) {
                display: none;
            }

            .mock-phone {
                position: relative;
                right: auto;
                bottom: auto;
                left: auto;
                width: min(190px, 64vw);
                transform: none;
                animation-name: floatPhoneMobile;
                margin-top: -2px;
            }

            .mock-receipt {
                display: none;
            }

            .mock-phone {
                display: none;
            }

            .testimonial-list {
                justify-content: flex-start;
            }

            .quote-text {
                min-height: 0;
                font-size: clamp(1.65rem, 8vw, 2.2rem);
            }

            .testimonial-item {
                grid-template-columns: 44px minmax(0, 1fr);
                min-height: 74px;
            }

            .testimonial-avatar {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                font-size: var(--text-sm);
            }

            .testimonial-name {
                font-size: var(--text-lg);
            }

            .section {
                padding: 42px 0;
            }

            .feature-showcase {
                min-height: 320px;
            }

            .showcase-main {
                left: 6%;
                top: 12%;
                width: 88%;
            }

            .showcase-side {
                right: 6%;
                bottom: 8%;
                width: 62%;
            }

            .workflow-visual,
            .story-grid {
                grid-template-columns: 1fr;
            }

            .workflow-visual {
                display: none;
            }

            .feature-grid {
                 grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .story-grid > :nth-child(n+2) {
                display: none;
            }

            .feature-tile {
                min-height: 150px;
                padding: 20px 12px;
                border-right: 1px solid rgba(215, 221, 232, .9);
            }

            .feature-grid-template .feature-tile:nth-last-child(2n) {
                border-right: 0;
            }

            .feature-grid-template .feature-tile:last-child (-n+2) {
                border-bottom: 0;
            }

            .testimonial-grid {
                padding: 28px 0 24px;
            }

            .testimonial-item {
                padding: 12px 0;
                min-height: 64px;
            }

            .footer::after {
                bottom: 0;
                font-size: 60px;
                opacity: .08;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                padding: 34px 0 26px;
                gap: 18px;
            }

            .footer-aside {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .workflow-card,
            .feature-tile {
                min-height: auto;
            }

            .cta-band {
                border-radius: 26px;
                padding: 40px 18px;
            }

            .cta-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .cta-actions .btn {
                width: 100%;
            }

            .partner-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .office-grid {
                grid-template-columns: 1fr;
            }

            .footer-copy-wrap {
                padding-top: 12px;
            }
        }

        @keyframes floatPhoneMobile {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes floatReceiptMobile {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="hero-nav">
            <div class="site-container nav-inner">
                <a href="{{ url('/') }}" class="brand-lockup" aria-label="Keijora POS">
                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="" class="brand-bird">
                    <span>
                        <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="brand-word">
                        <span class="brand-sub">POS System</span>
                    </span>
                </a>

                <nav class="nav-links" aria-label="Navigasi utama">
                    <a href="#fitur">Fitur</a>
                    <a href="#workflow">Workflow</a>
                    <a href="#demo">Demo</a>
                    <a href="#cerita">Cerita</a>
                </nav>

                @if (Route::has('login'))
                    <div class="nav-actions">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-secondary nav-login">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary">Coba Gratis</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </header>

        <main>
    <section class="hero">
        <video class="hero-video" autoplay muted loop playsinline poster="{{ asset('images/hero-sea.jpg') }}">
            <source src="{{ asset('videos/hero-bg.mp4') }}" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="site-container">
            <div class="reveal">
                        </div>
                        <h1 class="hero-title">Satu POS untuk mengelola kasir, stok, dan transaksi bisnis.</h1>
                        <p class="hero-copy">
                            <span class="hero-copy-desktop">Keijora POS membantu cafe, restoran, warung, dan bisnis F&B melayani pelanggan lebih cepat. Admin mengatur menu dan laporan, kasir fokus transaksi tanpa ribet.</span>
                            <span class="hero-copy-mobile">Keijora POS membantu cafe, restoran, warung, dan bisnis F&B melayani pelanggan lebih cepat. Admin mengatur menu dan laporan, kasir fokus transaksi tanpa ribet..</span>
                        </p>
                        <div class="hero-actions">
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-primary">
                                Mulai Gratis
                                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                            </a>
                            <a href="#demo" class="btn btn-secondary">Lihat Demo</a>
                        </div>
                    </div>

                    <div id="demo" class="hero-stage reveal delay-1">
                        <div class="hero-card">
                            <div class="hero-card-top">
                                <div>
                                    <strong style="display:block;color:var(--keijora-ink);font-size:var(--text-base);">Keijora POS Dashboard</strong>
                                    <span style="display:block;color:var(--keijora-muted);font-size:var(--text-sm);margin-top:3px;">Realtime kasir, produk, dan laporan</span>
                                </div>
                                <div class="window-dots" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>

                            <div class="hero-dashboard">
                                <aside class="mock-sidebar" aria-label="Mockup menu POS">
                                    <div class="mock-menu active">
                                        <span class="material-symbols-outlined" aria-hidden="true">point_of_sale</span>
                                        Kasir
                                    </div>
                                    <div class="mock-menu">
                                        <span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
                                        Produk
                                    </div>
                                    <div class="mock-menu">
                                        <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>
                                        Transaksi
                                    </div>
                                    <div class="mock-menu">
                                        <span class="material-symbols-outlined" aria-hidden="true">analytics</span>
                                        Laporan
                                    </div>
                                </aside>

                                <div class="mock-content">
                                    <div class="mock-search">
                                        <span class="material-symbols-outlined" aria-hidden="true">search</span>
                                        Cari produk atau barcode...
                                    </div>

                                    <div class="mock-grid">
                                        @foreach ([
                                            ['name' => 'Es Kopi Susu', 'price' => 'Rp 15.000', 'icon' => 'local_cafe'],
                                            ['name' => 'Mie Ayam', 'price' => 'Rp 18.000', 'icon' => 'ramen_dining'],
                                            ['name' => 'Es Teh', 'price' => 'Rp 5.000', 'icon' => 'local_drink'],
                                            ['name' => 'Bakwan', 'price' => 'Rp 2.500', 'icon' => 'fastfood'],
                                            ['name' => 'Nasi Bowl', 'price' => 'Rp 22.000', 'icon' => 'rice_bowl'],
                                            ['name' => 'Topping', 'price' => '+Rp 3.000', 'icon' => 'add_circle'],
                                        ] as $product)
                                            <div class="mock-product">
                                                <div class="mock-product-image">
                                                    <span class="material-symbols-outlined" aria-hidden="true">{{ $product['icon'] }}</span>
                                                </div>
                                                <div class="mock-product-name">{{ $product['name'] }}</div>
                                                <div class="mock-product-price money">{{ $product['price'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mock-receipt" aria-label="Mockup struk">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="" style="width:26px;height:26px;object-fit:contain;">
                                <strong style="font-size:var(--text-sm);color:var(--keijora-navy);">Nota Digital</strong>
                            </div>
                            <div class="receipt-line dark" style="width:58%;"></div>
                            <div class="receipt-line" style="width:100%;margin-top:8px;"></div>
                            <div class="receipt-line" style="width:78%;margin-top:8px;"></div>
                            <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-top:14px;font-size:var(--text-xs);font-weight:700;">
                                <span>Total</span>
                                <span class="money" style="color:var(--keijora-navy);">Rp 38.000</span>
                            </div>
                        </div>

                        <div class="mock-phone" aria-label="Mockup WhatsApp receipt">
                            <div class="mock-phone-screen">
                                <strong style="display:block;font-size:var(--text-sm);color:var(--keijora-navy);">Kirim Nota</strong>
                                <div class="chat-row left">Halo Kak, total pesanan sudah siap.</div>
                                <div class="chat-row right">Link Nota: /r/NotaCafe</div>
                                <div class="chat-row left">Terima kasih, selamat menikmati pesanan Kakak.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="trust-strip">
                <div class="site-container trust-inner">
                    <p class="trust-copy">Dirancang untuk membantu operasional F&B lebih cepat, rapi, dan mudah dipantau oleh owner.</p>
                    <div class="logo-row" aria-label="Kategori bisnis">
                        <span class="logo-pill">Cafe</span>
                        <span class="logo-pill">Restoran</span>
                        <span class="logo-pill">Warung</span>
                        <span class="logo-pill">Kedai Kopi</span>
                        <span class="logo-pill">Cloud Kitchen</span>
                    </div>
                </div>
            </section>
        </main>

    <section id="cerita" class="testimonial-band">
        <div class="site-container testimonial-grid">
            <div class="testimonial-quote reveal">
                <blockquote class="quote-text" data-testimonial-quote>"Kasir lebih fokus melayani, admin bisa membaca transaksi tanpa menunggu rekap manual."</blockquote>
                <div class="quote-source" data-testimonial-source>Pemilik Cafe Lokal</div>
            </div>

            <div class="testimonial-list reveal delay-1" data-testimonial-list>
                <div class="testimonial-item is-active" data-quote='"Kasir lebih fokus melayani, admin bisa membaca transaksi tanpa menunggu rekap manual."' data-source="Pemilik Cafe Lokal" data-avatar="PL">
                    <div class="testimonial-avatar">PL</div>
                    <div class="min-w-0">
                        <div class="testimonial-name">Pemilik Cafe Lokal</div>
                        <div class="testimonial-role">Workflow Keijora dibuat agar tim kecil tetap bisa punya sistem yang terlihat profesional.</div>
                    </div>
                </div>
                <div class="testimonial-item" data-quote='"Respon pelanggan jadi lebih cepat dan antrian jauh lebih mudah dikontrol."' data-source="Admin Operasional" data-avatar="AO">
                    <div class="testimonial-avatar">AO</div>
                    <div class="min-w-0">
                        <div class="testimonial-name">Admin Operasional</div>
                        <div class="testimonial-role">Panel kasir dan laporan membuat monitoring lebih ringan dipakai setiap hari.</div>
                    </div>
                </div>
                <div class="testimonial-item" data-quote='"Template nota dan transaksi terasa rapi, jadi customer lebih percaya."' data-source="Owner Warung Kopi" data-avatar="OW">
                    <div class="testimonial-avatar">OW</div>
                    <div class="min-w-0">
                        <div class="testimonial-name">Owner Warung Kopi</div>
                        <div class="testimonial-role">Struk, WhatsApp, dan data menu lebih mudah dijaga dalam satu sistem.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="section">
        <div class="site-container">
            <div class="section-head reveal">
                <p class="section-kicker">Operasional Kasir</p>
                <h2 class="section-title">Jualan otomatis, transaksi rapi, langsung dari layar kasir.</h2>
                <p class="section-copy">Produk, varian, add-on, diskon, metode bayar, dan nota digital dibuat menyatu agar kasir bisa bergerak cepat saat antrean ramai.</p>
            </div>

            <div class="split-grid">
                <div class="reveal delay-1">
                    <h3 style="margin:0;color:var(--keijora-ink);font-size:var(--text-xl);font-weight:800;line-height:1.2;">Tanpa proses manual, semua transaksi tetap tercatat.</h3>
                    <p class="section-copy">Setiap checkout langsung masuk ke histori transaksi, stok berkurang, dan laporan bisa dibaca admin sesuai tenant/cafe masing-masing.</p>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;">
                        <span class="metric-pill">Cart AJAX</span>
                        <span class="metric-pill">Receipt PNG</span>
                        <span class="metric-pill">Multi Tenant</span>
                    </div>
                </div>

                <div class="feature-showcase reveal delay-2">
                    <div class="showcase-card showcase-main">
                        <div class="mini-row">
                            <div class="mini-icon"><span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span></div>
                            <div>
                                <strong style="display:block;font-size:var(--text-sm);">Keranjang cepat</strong>
                                <span style="display:block;color:var(--keijora-muted);font-size:var(--text-xs);margin-top:3px;">Tambah item tanpa reload halaman</span>
                            </div>
                        </div>
                        <div class="mini-row" style="margin-top:10px;">
                            <div class="mini-icon"><span class="material-symbols-outlined" aria-hidden="true">payments</span></div>
                            <div>
                                <strong style="display:block;font-size:var(--text-sm);">Pembayaran tunai / QRIS</strong>
                                <span style="display:block;color:var(--keijora-muted);font-size:var(--text-xs);margin-top:3px;">Diskon dan kembalian langsung dihitung</span>
                            </div>
                        </div>
                    </div>
                    <div class="showcase-card showcase-side">
                        <strong style="display:block;color:var(--keijora-navy);font-size:var(--text-sm);">Live Report</strong>
                        <p style="margin:8px 0 0;color:var(--keijora-muted);font-size:var(--text-xs);line-height:1.6;">Penjualan hari ini, produk terlaris, dan transaksi terbaru.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="workflow" class="section" style="background:#fbfdff;border-top:1px solid var(--keijora-line);border-bottom:1px solid var(--keijora-line);">
        <div class="site-container">
            <div class="section-head reveal">
                <p class="section-kicker">Workflow</p>
                <h2 class="section-title">Satu alur dari produk sampai nota WhatsApp.</h2>
                <p class="section-copy">Alur dibuat sederhana untuk kasir, tetapi tetap lengkap untuk admin dan owner.</p>
            </div>

            <div class="workflow-visual">
                @foreach ([
                    ['icon' => 'inventory_2', 'title' => 'Produk', 'text' => 'Kategori, stok, varian, add-on.'],
                    ['icon' => 'point_of_sale', 'title' => 'Kasir', 'text' => 'Pilih menu dan catatan pesanan.'],
                    ['icon' => 'payments', 'title' => 'Bayar', 'text' => 'Tunai, QRIS, diskon, kembalian.'],
                    ['icon' => 'receipt_long', 'title' => 'Nota', 'text' => 'Cetak atau kirim link publik.'],
                    ['icon' => 'analytics', 'title' => 'Laporan', 'text' => 'Dashboard dan histori transaksi.'],
                ] as $flow)
                    <article class="workflow-card reveal">
                        <div class="workflow-icon">
                            <span class="material-symbols-outlined" aria-hidden="true">{{ $flow['icon'] }}</span>
                        </div>
                        <h3 class="workflow-title">{{ $flow['title'] }}</h3>
                        <p class="workflow-text">{{ $flow['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container">
            <div class="split-grid feature-split reveal">
                <div class="feature-copy-panel">
                    <p class="section-kicker">Modul POS</p>
                    <h2 class="section-title">Ubah operasional cafe jadi lebih tenang.</h2>
                    <p class="section-copy">Keijora menyatukan kebutuhan harian bisnis F&B dalam satu sistem: kasir, produk, transaksi, user kasir, dan pengaturan struk.</p>
                    <div class="hero-actions feature-actions">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-primary">Daftar Sekarang</a>
                        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-secondary">Masuk POS</a>
                    </div>
                </div>

                <div class="feature-grid feature-grid-template">
                    @foreach ([
                        ['icon' => 'point_of_sale', 'title' => 'Kasir'],
                        ['icon' => 'inventory_2', 'title' => 'Produk'],
                        ['icon' => 'receipt_long', 'title' => 'Transaksi'],
                        ['icon' => 'monitoring', 'title' => 'Dashboard'],
                    ] as $module)
                        <article class="feature-tile">
                            <div class="feature-icon">
                                <span class="material-symbols-outlined" aria-hidden="true">{{ $module['icon'] }}</span>
                            </div>
                            <h3 class="feature-title">{{ $module['title'] }}</h3>
                            <a class="feature-link" href="{{ Route::has('register') ? route('register') : '#' }}">Lihat detail produk</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:20px;">
        <div class="site-container">
            <div class="section-head reveal">
                <p class="section-kicker">Kenapa Keijora</p>
                <h2 class="section-title">Detail kecil yang terasa saat dipakai kasir.</h2>
                <p class="section-copy">Tampilan dibuat lebih tenang dan tidak penuh kotak agar perhatian tetap tertuju ke alur transaksi utama.</p>
            </div>

            <div class="story-layout">
                <div class="story-media reveal">
                    <span class="story-pill one"><span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span></span>
                    <span class="story-pill two"><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span></span>
                    <span class="story-pill three"><span class="material-symbols-outlined" aria-hidden="true">payments</span></span>
                    <span class="story-pill four"><span class="material-symbols-outlined" aria-hidden="true">inventory_2</span></span>
                    <span class="story-pill five"><span class="material-symbols-outlined" aria-hidden="true">analytics</span></span>
                </div>

                <div class="story-grid">
                    @foreach ([
                        ['title' => 'Cepat di koneksi lambat', 'text' => 'Aksi cart memakai AJAX sehingga layar tidak reload penuh setiap tambah item.'],
                        ['title' => 'Responsive multi-device', 'text' => 'Tampilan kasir disiapkan untuk phone, tablet portrait, tablet landscape, dan laptop.'],
                        ['title' => 'Nota mudah dibagikan', 'text' => 'Receipt publik bisa dikirim melalui WhatsApp dengan wording yang hangat.'],
                    ] as $story)
                        <article class="story-item reveal">
                            <h3 style="margin:0;color:var(--keijora-navy);font-size:var(--text-xl);font-weight:800;">{{ $story['title'] }}</h3>
                            <p style="margin:12px 0 0;color:var(--keijora-muted);font-size:var(--text-base);line-height:1.8;">{{ $story['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="site-container" style="padding:32px 0 0;">
        <div class="cta-band reveal">
            <div class="cta-band-content">
                <p class="cta-kicker">Mulai operasional lebih rapi</p>
                <h2 class="cta-title">Ubah setiap percakapan jadi penjualan yang tercatat rapi.</h2>
                <p class="cta-copy">Daftarkan bisnis, aktifkan tenant, lalu kelola kasir, produk, transaksi, dan laporan dari satu workspace Keijora.</p>
                <div class="hero-actions cta-actions">
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-primary">Coba Demo Sekarang</a>
                    <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-secondary">Mulai Gratis</a>
                </div>
            </div>
        </div>
    </section>

    <section class="partner-strip">
        <div class="site-container partner-inner">
            <div class="partner-brand">
                <span class="partner-badge"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">groups</span></span>
                <span>Keijora POS is trusted by growing F&B teams</span>
            </div>
            <div class="partner-text">Kasir, stok, laporan, dan nota digital dalam satu sistem.</div>
        </div>
    </section>

    <footer class="footer">
        <div class="site-container footer-grid">
            <div class="footer-brand-block">
                <div>
                    <a href="{{ url('/') }}" class="brand-lockup">
                        <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="" class="brand-bird">
                        <span>
                            <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="brand-word">
                            <span class="brand-sub">POS System</span>
                        </span>
                    </a>
                    <p style="max-width:420px;margin:18px 0 0;color:var(--keijora-muted);font-size:var(--text-base);line-height:1.8;">Keijora POS membantu bisnis F&B menjalankan kasir, produk, transaksi, dan laporan dalam satu sistem yang ringan dipakai setiap hari.</p>
                </div>

                <div class="footer-social" aria-label="Sosial media">
                    <span class="social-icon"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">language</span></span>
                    <span class="social-icon"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">photo_camera</span></span>
                    <span class="social-icon"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">smart_display</span></span>
                    <span class="social-icon"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">forum</span></span>
                </div>

                <div class="footer-tabs" aria-label="Office tabs">
                    <span class="footer-tab is-active">ID Indonesia</span>
                </div>
            </div>

            <div class="footer-aside">
                <div class="download-block">
                    <p class="download-title">Download Keijora Mobile App</p>
                    <div class="store-badges">
                        <span class="store-badge">Google Play</span>
                        <span class="store-badge">App Store</span>
                    </div>
                </div>

                <div class="footer-links-block">
                    <p class="footer-links-title">Product</p>
                    <div class="footer-links-grid">
                        <div class="footer-links-list">
                            <a href="#fitur">Kasir</a>
                            <a href="#workflow">Produk</a>
                            <a href="#demo">Workflow</a>
                            <a href="#cerita">Laporan</a>
                        </div>
                        <div class="footer-links-list">
                            <a href="{{ Route::has('login') ? route('login') : '#' }}">Terms & Conditions</a>
                            <a href="{{ Route::has('register') ? route('register') : '#' }}">Kebijakan Privasi</a>
                            <a href="{{ Route::has('login') ? route('login') : '#' }}">Refund & Delivery</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-divider">
            <div class="site-container footer-copy-wrap">
                <p class="footer-copy">Copyright © 2026 Keijora POS. All rights reserved.</p>
            </div>
        </div>
    </footer>
    </div>

    <script>
        (() => {
            const nav = document.querySelector('.hero-nav');
            const onScroll = () => {
                if (!nav) {
                    return;
                }
                nav.classList.toggle('is-scrolled', window.scrollY > 4);
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            const quoteEl = document.querySelector('[data-testimonial-quote]');
            const sourceEl = document.querySelector('[data-testimonial-source]');
            const items = Array.from(document.querySelectorAll('[data-testimonial-list] .testimonial-item'));
            if (!quoteEl || !sourceEl || !items.length) {
                return;
            }

            let activeIndex = 0;
            let timer = null;

            const setActive = (index) => {
                activeIndex = index;
                items.forEach((item, itemIndex) => {
                    item.classList.toggle('is-active', itemIndex === index);
                });

                const item = items[index];
                quoteEl.classList.add('is-changing');
                sourceEl.classList.add('is-changing');

                window.setTimeout(() => {
                    quoteEl.textContent = item.dataset.quote || '';
                    sourceEl.textContent = item.dataset.source || '';
                    quoteEl.classList.remove('is-changing');
                    sourceEl.classList.remove('is-changing');
                }, 180);
            };

            const startRotation = () => {
                stopRotation();
                timer = window.setInterval(() => {
                    setActive((activeIndex + 1) % items.length);
                }, 3200);
            };

            const stopRotation = () => {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
            };

            items.forEach((item, index) => {
                item.addEventListener('click', () => {
                    setActive(index);
                    startRotation();
                });
                item.addEventListener('mouseenter', stopRotation);
                item.addEventListener('mouseleave', startRotation);
            });

            setActive(0);
            startRotation();
        })();
    </script>
</body>
</html>
