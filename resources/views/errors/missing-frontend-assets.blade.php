<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frontend assets missing — Cartxis</title>
    <style>
        :root {
            --bg: #0f1419;
            --panel: #1a2332;
            --text: #e8eef7;
            --muted: #9aa8bc;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --border: #2d3a4d;
            --warn: #f59e0b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(ellipse at top, #1e293b 0%, var(--bg) 55%);
            color: var(--text);
            line-height: 1.55;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
        }
        .card {
            width: 100%;
            max-width: 40rem;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem 1.75rem;
        }
        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
            font-weight: 650;
            letter-spacing: -0.02em;
        }
        .badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--warn);
            margin-bottom: 0.75rem;
        }
        p { margin: 0 0 1rem; color: var(--muted); }
        ol { margin: 0 0 1.25rem; padding-left: 1.25rem; color: var(--text); }
        li { margin-bottom: 0.65rem; }
        li strong { color: var(--text); }
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85em;
            background: #0b1220;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 0.1em 0.35em;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        a.btn {
            display: inline-block;
            text-decoration: none;
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
        }
        a.btn:hover { background: var(--accent-hover); }
        a.link {
            color: var(--accent);
            align-self: center;
            font-size: 0.9rem;
        }
        .note {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="card" role="main">
        <div class="badge">Setup required</div>
        <h1>Frontend assets are missing</h1>
        <p>
            Cartxis could not find <code>public/build/manifest.json</code>.
            The admin panel and setup wizard need pre-built Vite assets.
            Shared hosts typically do not include Node.js, so cloning the git repo alone is not enough.
        </p>

        <ol>
            <li>
                <strong>Preferred — Shared Hosting package (no Node required)</strong><br>
                Download the latest <em>Shared Hosting</em> release zip from
                <a class="link" href="https://github.com/cartxis/cartxis/releases">GitHub Releases</a>.
                That zip includes <code>public/build</code> and (usually) <code>vendor</code>.
                Upload it, point your document root to <code>public/</code>, then open your site.
            </li>
            <li>
                <strong>Or build assets yourself</strong> (Node.js 18+ on your machine or CI):
                <br><code>npm install &amp;&amp; npm run build</code>
                <br>Then upload the generated <code>public/build</code> folder, or run
                <code>php artisan cartxis:install</code> again.
            </li>
        </ol>

        <div class="actions">
            <a class="btn" href="https://github.com/cartxis/cartxis/releases">Download Shared Hosting release</a>
            <a class="link" href="https://github.com/cartxis/cartxis/blob/main/docs/SHARED_HOSTING.md">Shared hosting docs</a>
        </div>

        <p class="note">
            HTTP 503 — temporary until assets are present. After fixing, refresh this page or visit
            <code>/setup</code>. Developers may use <code>php artisan cartxis:install --skip-assets</code>
            only for non-browser CI/dev workflows.
        </p>
    </main>
</body>
</html>
