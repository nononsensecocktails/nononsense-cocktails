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
    <title>No-Nonsense Cocktails<?php echo isset($page_title) ? ' - ' . $page_title : ''; ?></title>

<?php
// Dynamic OG tags for recipe shares so pasted links show recipe name + source
$name  = isset($_GET['name'])  ? trim($_GET['name'])  : '';
$source = isset($_GET['source']) ? trim($_GET['source']) : '';

if ($name && $source) {
    $ogTitle = htmlspecialchars($name . ' from ' . $source . ' — No-Nonsense Cocktails', ENT_QUOTES, 'UTF-8');
    echo '<meta property="og:title" content="' . $ogTitle . '">' . "\n";
    echo '<meta property="og:site_name" content="No-Nonsense Cocktails">' . "\n";
    echo '<meta property="og:url" content="' . htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
    // Optional but nice: add a description if you want
    // echo '<meta property="og:description" content="Cocktail recipe from No-Nonsense Cocktails">' . "\n";
}
?>

    
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
