<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $nav_categories_stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
    $nav_categories = $nav_categories_stmt->fetchAll();
} catch (Exception $e) {
    $nav_categories = [];
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? $page_title . " | NSBM NewsDesk" : "NSBM NewsDesk - Campus News Management System";

$root = get_root_prefix();
$user_prefix = (strpos($_SERVER['PHP_SELF'] ?? '', '/user/') !== false) ? '' : 'user/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link rel="icon" type="image/jpeg" href="<?php echo $root; ?>assets/images/logo_newsfeed.jpg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css?v=<?php echo time(); ?>">

    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('nsbm_theme');
                const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = savedTheme ? savedTheme : (systemDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (e) {}
        })();
    </script>
</head>
<body>

    <div class="top-header-bar d-none d-md-block">
        <div class="container-fluid px-3 px-xl-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span><i class="bi bi-geo-alt me-1 text-warning"></i> NSBM Green University Town, Pitipana, Homagama</span>
                    <span><i class="bi bi-telephone me-1 text-warning"></i> +94 (11) 544 5000</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-calendar-event me-1"></i> <?php echo date('l, F j, Y'); ?></span>
                    <a href="https://www.nsbm.ac.lk" target="_blank" class="fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i> Official Website</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-nsbm py-2">
        <div class="container-fluid px-2 px-md-3 px-xl-4 d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center flex-shrink-0 me-2 me-xl-3" href="<?php echo $root; ?>index.php">
                <img src="<?php echo $root; ?>assets/images/logo_nsbm.png" alt="NSBM Green University Logo" style="height: 38px; width: auto;">
                <div class="brand-text-container d-none d-sm-block">
                    <div class="brand-title">NewsDesk</div>
                    <div class="brand-subtitle d-none d-xl-block">Campus News Management</div>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <?php if (is_logged_in()): ?>

                    <ul class="navbar-nav me-auto mb-0 ms-lg-1 ms-xl-2 flex-nowrap align-items-center gap-1">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $root; ?>index.php">
                                <i class="bi bi-newspaper"></i> News Feed
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'news.php') ? 'active' : ''; ?>" href="<?php echo $root; ?>news.php">
                                <i class="bi bi-collection"></i> All Stories
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'category.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-grid"></i> Categories
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm">
                                <?php foreach ($nav_categories as $cat): ?>
                                    <li>
                                        <a class="dropdown-item py-2" href="<?php echo $root; ?>category.php?slug=<?php echo urlencode($cat['slug']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>" href="<?php echo $root; ?>announcements.php">
                                <i class="bi bi-megaphone"></i> Announcements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'events.php') ? 'active' : ''; ?>" href="<?php echo $root; ?>events.php">
                                <i class="bi bi-calendar3"></i> Events
                            </a>
                        </li>
                    </ul>

                    <form class="d-flex my-0 navbar-search-form" action="<?php echo $root; ?>search.php" method="GET" role="search">
                        <div class="input-group navbar-search-group">
                            <input class="form-control rounded-start-pill border-end-0 ps-3 navbar-search-input" 
                                   type="search" 
                                   name="q" 
                                   placeholder="Search..." 
                                   value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                                   aria-label="Search" 
                                   autocomplete="off">
                            <button class="btn rounded-end-pill border-start-0 pe-2.5 navbar-search-btn" type="submit" aria-label="Search">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                <?php else: ?>

                    <ul class="navbar-nav me-auto mb-0"></ul>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-2 ms-auto flex-nowrap navbar-actions-group">

                    <button class="btn theme-toggle-btn rounded-circle" type="button" aria-label="Toggle Theme" title="Toggle Theme">
                        <i class="bi bi-moon-stars-fill theme-toggle-icon"></i>
                        <span class="theme-toggle-text d-none">Theme</span>
                    </button>

                    <?php if (is_logged_in()): ?>
                        <?php $user = current_user(); ?>
                        <?php if (is_admin()): ?>
                            <a href="<?php echo $root; ?>admin/dashboard.php" class="btn btn-outline-danger navbar-cta-btn">
                                <i class="bi bi-speedometer2"></i> <span>Admin<span class="d-none d-xl-inline"> Panel</span></span>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $user_prefix; ?>submit_article.php" class="btn btn-nsbm navbar-cta-btn">
                                <i class="bi bi-pencil-square"></i> <span>Submit<span class="d-none d-xl-inline"> Story</span></span>
                            </a>
                        <?php endif; ?>

                        <div class="dropdown">
                            <button class="btn navbar-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle text-success fs-6"></i>
                                <span class="d-inline-block text-truncate" style="max-width: 95px;"><?php echo htmlspecialchars($user['name']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li class="px-3 py-1">
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($user['email']); ?></small>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase"><?php echo htmlspecialchars($user['role']); ?></span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (!is_admin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo $user_prefix; ?>dashboard.php"><i class="bi bi-grid-1x2 me-2"></i> My Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $user_prefix; ?>bookmarks.php"><i class="bi bi-bookmark me-2"></i> Saved Articles</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $user_prefix; ?>my_submissions.php"><i class="bi bi-journal-text me-2"></i> My Submissions</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $user_prefix; ?>subscriptions.php"><i class="bi bi-bell me-2"></i> My Subscriptions</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $user_prefix; ?>profile.php"><i class="bi bi-gear me-2"></i> Profile Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="<?php echo $root; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $root; ?>login.php" class="btn btn-outline-secondary navbar-cta-btn">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="<?php echo $root; ?>register.php" class="btn btn-nsbm navbar-cta-btn">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

