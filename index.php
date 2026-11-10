<?php

/**
 * ============================================================================
 * SUSI PARSEL LEBARAN (SPL) - Root Redirector for XAMPP / htdocs
 * ============================================================================
 * Mengarahkan akses dari folder root (misal: http://localhost/SPL2026/)
 * langsung ke folder public/ aplikasi Laravel.
 */

$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseDir = str_replace('\\', '/', rtrim($scriptDir, '/\\'));

if ($baseDir === '.' || $baseDir === '/' || $baseDir === '') {
    $baseDir = '';
}

$targetUrl = ($baseDir ? $baseDir : '') . '/public/';

if (!empty($_SERVER['QUERY_STRING'])) {
    $targetUrl .= '?' . $_SERVER['QUERY_STRING'];
}

if (!headers_sent()) {
    header('Location: ' . $targetUrl, true, 302);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>">
    <title>Mengarahkan ke Susi Parsel Lebaran (SPL)...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
        }
        .card {
            text-align: center;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            max-width: 420px;
        }
        .spinner {
            border: 3px solid #e2e8f0;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border-left-color: #16a34a;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1.5rem auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h1 { margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 700; color: #15803d; }
        p { margin: 0; color: #64748b; font-size: 0.95rem; }
        a { display: inline-block; margin-top: 1.25rem; color: #16a34a; text-decoration: none; font-weight: 500; font-size: 0.9rem; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>Susi Parsel Lebaran (SPL)</h1>
        <p>Mengarahkan ke sistem aplikasi...</p>
        <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>">Klik di sini jika tidak beralih otomatis</a>
    </div>
    <script>
        window.location.href = <?= json_encode($targetUrl) ?>;
    </script>
</body>
</html>
<?php
exit;

