<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php
// === Dynamic title + Open Graph tags for recipe share links ===
$name   = isset($_GET['name'])   ? trim($_GET['name'])   : '';
$source = isset($_GET['source']) ? trim($_GET['source']) : '';

if ($name && $source) {
    $displayTitle = $name . ' from ' . $source;
    $page_title = $displayTitle;           // This makes the <title> tag dynamic too

    $ogTitle = htmlspecialchars($displayTitle . ' — No-Nonsense Cocktails', ENT_QUOTES, 'UTF-8');
    
    echo '<title>No-Nonsense Cocktails - ' . htmlspecialchars($displayTitle, ENT_QUOTES, 'UTF-8') . '</title>' . "\n";
    
    echo '<meta property="og:title" content="' . $ogTitle . '">' . "\n";
    echo '<meta property="og:site_name" content="No-Nonsense Cocktails">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $fullUrl  = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    echo '<meta property="og:url" content="' . htmlspecialchars($fullUrl, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    
    // Reliable fallback image (your existing logo). Apple likes having an image.
    echo '<meta property="og:image" content="https://nononsensecocktails.com/images/Coldberry_01_TM.jpg">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    
    echo '<meta property="og:description" content="Cocktail recipe from No-Nonsense Cocktails">' . "\n";
} else {
    // Default title when not viewing a specific recipe
    echo '<title>No-Nonsense Cocktails</title>' . "\n";
}
?>

    <!-- Favicon / Icon links for modern browsers -->
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/android-chrome-512x512.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        :root { --accent: #e76f51; }
        @media (prefers-color-scheme: dark) { :root { --accent: #f4a261; } }
        body {
            background: #f8f1e3;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.85rem;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #1e1e1e; color: #e0e0e0; }
        }
        .main-container {
            width: 800px !important;
            max-width: 800px !important;
            min-width: 800px !important;
            margin: 2px auto 20px auto;
            padding: 0;
        }
        .navbar {
            background: #2a2a2a;
            color: white;
            padding: 8px 15px;
            font-size: 0.9rem;
            width: 800px !important;
            max-width: 800px !important;
            margin: 0 auto;
        }
        .navbar a { color: white; text-decoration: none; }
        .navbar a:hover { color: #f4a261; }
        .card { border: 1px solid #ccc; border-radius: 0; }
        @media (prefers-color-scheme: dark) {
            .card { border-color: #555; background-color: #2a2a2a; color: #e0e0e0; }
        }
    </style>
</head>
<body>
    <!-- Simplified Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid d-flex align-items-center" style="max-width:800px;margin:0 auto;">
            <div class="d-flex align-items-center">
                <a href="index.php">
                    <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height:36px;" class="me-2">
                </a>
                <h1 class="h5 mb-0 text-white">No-Nonsense Cocktails</h1>
            </div>
        </div>
    </nav>

    <div class="main-container">
