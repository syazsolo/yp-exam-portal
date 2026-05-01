<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $diag['title'] }} — YP Examination Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@1,300&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --cream: #edeae3;
            --cream2: #e5e0d7;
            --ink: #1a1714;
            --crimson: #8b1a2c;
            --muted: #948d84;
            --border: #d5cfc6;
            --surface: #f5f2ec;
        }
        html, body { height: 100%; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cream); color: var(--ink); }
        a { color: inherit; text-decoration: none; }

        .page { min-height: 100vh; display: flex; flex-direction: column; }

        nav.top { display: flex; align-items: center; justify-content: space-between; padding: 26px 52px; }
        .brand { display: flex; align-items: center; gap: 9px; }
        .brand .yp { font-family: 'Cormorant Garamond', serif; font-style: italic; font-weight: 300; font-size: 21px; letter-spacing: -0.01em; line-height: 1; }
        .brand .label { font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase; font-weight: 500; color: var(--muted); }
        .nav-link { font-size: 13px; color: var(--muted); transition: color 0.2s; }
        .nav-link:hover { color: var(--ink); }

        main { flex: 1; display: flex; align-items: center; padding: 0 52px 80px; }
        .content { max-width: 720px; width: 100%; }

        .head-row { display: flex; align-items: center; gap: 18px; margin-bottom: 28px; }
        .icon { color: var(--muted); display: flex; }
        .divider { width: 1px; height: 40px; background: var(--border); }
        .code { font-family: 'Cormorant Garamond', serif; font-style: italic; font-weight: 300; font-size: 48px; color: var(--border); line-height: 1; letter-spacing: -0.02em; }

        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-style: italic;
            font-size: clamp(40px, 5vw, 64px);
            line-height: 1.0;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 20px;
        }

        .detail { font-size: 14px; font-weight: 300; color: var(--muted); line-height: 1.8; margin-bottom: 12px; max-width: 540px; }

        .hint {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 12px 16px;
            background: var(--surface);
            border-left: 2px solid var(--border);
            margin-bottom: 36px;
            max-width: 540px;
        }
        .hint svg { flex-shrink: 0; margin-top: 2px; }
        .hint p { font-size: 12px; font-weight: 400; color: var(--muted); line-height: 1.7; }

        .fix-label {
            font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
            font-weight: 500; color: var(--muted); margin-bottom: 18px;
        }

        ol.steps { list-style: none; counter-reset: step; margin-bottom: 36px; }
        ol.steps li {
            counter-increment: step;
            display: flex; gap: 16px;
            padding: 14px 0;
            border-top: 1px solid var(--border);
        }
        ol.steps li:last-child { border-bottom: 1px solid var(--border); }
        ol.steps li::before {
            content: counter(step, decimal-leading-zero);
            font-family: 'Cormorant Garamond', serif;
            font-style: italic; font-weight: 300;
            font-size: 18px; color: var(--border);
            flex-shrink: 0; min-width: 28px; line-height: 1.6;
        }
        .step-body { flex: 1; }
        .step-text { font-size: 13px; color: var(--ink); line-height: 1.7; font-weight: 400; }
        .step-cmd {
            display: inline-block;
            margin-top: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px; font-weight: 500;
            color: var(--ink);
            background: var(--cream2);
            padding: 5px 10px;
            border: 1px solid var(--border);
            user-select: all;
        }

        .actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .btn {
            font-family: inherit;
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.08em; text-transform: uppercase;
            padding: 12px 28px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1.5px solid transparent;
        }
        .btn-primary { background: var(--ink); color: var(--cream); border-color: var(--ink); }
        .btn-primary:hover { background: var(--crimson); border-color: var(--crimson); }
        .btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
        .btn-ghost:hover { color: var(--ink); border-color: var(--ink); }

        details.tech {
            margin-top: 48px; max-width: 720px;
            border-top: 1px solid var(--border); padding-top: 20px;
        }
        details.tech summary {
            font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
            font-weight: 500; color: var(--muted); cursor: pointer; list-style: none;
        }
        details.tech summary::-webkit-details-marker { display: none; }
        details.tech summary::after { content: ' +'; }
        details.tech[open] summary::after { content: ' −'; }
        details.tech pre {
            margin-top: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px; line-height: 1.6;
            color: var(--ink);
            background: var(--surface);
            border-left: 2px solid var(--border);
            padding: 14px 16px;
            white-space: pre-wrap; word-break: break-word;
            max-height: 240px; overflow: auto;
        }

        footer { padding: 24px 52px; display: flex; justify-content: flex-end; }
        footer span { font-size: 9px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--muted); }

        @media (max-width: 640px) {
            nav.top, main, footer { padding-left: 24px; padding-right: 24px; }
        }
    </style>
</head>
<body>
<div class="page">
    <nav class="top">
        <a href="/" class="brand">
            <span class="yp">yp</span>
            <span class="label">Examination Portal</span>
        </a>
        <a href="/" class="nav-link">Go home</a>
    </nav>

    <main>
        <div class="content">
            <div class="head-row">
                <span class="icon">{!! $diag['icon'] !!}</span>
                <div class="divider"></div>
                <span class="code">{{ $diag['code'] }}</span>
            </div>

            <h1>{{ $diag['title'] }}.</h1>

            <p class="detail">{{ $diag['subtitle'] }}</p>

            <div class="hint">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#948d84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <p>{{ $diag['cause'] }}</p>
            </div>

            @if (!empty($diag['steps']))
                <p class="fix-label">How to fix</p>
                <ol class="steps">
                    @foreach ($diag['steps'] as $step)
                        <li>
                            <div class="step-body">
                                <div class="step-text">{{ $step['text'] }}</div>
                                @if (!empty($step['cmd']))
                                    <code class="step-cmd">{{ $step['cmd'] }}</code>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            <div class="actions">
                <button class="btn btn-primary" onclick="window.location.reload()">↻ Retry</button>
                <a href="/" class="btn btn-ghost">Go home</a>
            </div>

            @if (config('app.debug') && !empty($technical))
                <details class="tech">
                    <summary>Technical detail</summary>
                    <pre>{{ $technical }}</pre>
                </details>
            @endif
        </div>
    </main>

    <footer>
        <span>Mohamad Syazani — 2026</span>
    </footer>
</div>
</body>
</html>
