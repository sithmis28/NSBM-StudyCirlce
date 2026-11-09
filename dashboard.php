<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access your student dashboard.');
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$user_id = $user['id'];

$bookmark_count = $pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
$bookmark_count->execute([$user_id]);
$total_bookmarks = $bookmark_count->fetchColumn();

$sub_count = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ?");
$sub_count->execute([$user_id]);
$total_subscriptions = $sub_count->fetchColumn();

$submission_count = $pdo->prepare("SELECT COUNT(*) FROM article_submissions WHERE user_id = ?");
$submission_count->execute([$user_id]);
$total_submissions = $submission_count->fetchColumn();

$feed_stmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
    FROM news n
    JOIN categories c ON n.category_id = c.id
    JOIN subscriptions s ON s.category_id = c.id
    LEFT JOIN users u ON n.author_id = u.id
    WHERE s.user_id = ? AND n.status = 'published'
    ORDER BY n.created_at DESC
    LIMIT 6
");
$feed_stmt->execute([$user_id]);
$personalized_feed = $feed_stmt->fetchAll();

$my_submissions_stmt = $pdo->prepare("
    SELECT a.*, c.name AS category_name
    FROM article_submissions a
    JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.submitted_at DESC
    LIMIT 3
");
$my_submissions_stmt->execute([$user_id]);
$recent_submissions = $my_submissions_stmt->fetchAll();

$page_title = "Student Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Dashboard</li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="p-4 p-md-5 rounded-4 border shadow-sm mb-4 dashboard-welcome-banner" style="background: linear-gradient(135deg, #2563A6 0%, #2E7D5B 100%); color: white;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow dashboard-avatar-circle" style="width: 64px; height: 64px; font-size: 1.8rem; font-weight: bold; background-color: #ffffff; color: #2E7D5B;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <div class="d-flex flex-wrap gap-2 align-items-center small text-light-subtle">
                        <span><i class="bi bi-mortarboard-fill text-warning me-1"></i> <?php echo htmlspecialchars($user['faculty']); ?></span>
                        <?php if (!empty($user['student_id'])): ?>
                            <span>&bull;</span>
                            <span><i class="bi bi-card-text me-1"></i> ID: <?php echo htmlspecialchars($user['student_id']); ?></span>
                        <?php endif; ?>
                        <span>&bull;</span>
                        <span><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>
            </div>
            <div>
                <a href="submit_article.php" class="btn btn-nsbm rounded-pill px-4 shadow">
                    <i class="bi bi-plus-circle me-1"></i> Submit Article
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Saved Articles</span>
                        <h2 class="fw-bold text-navy mb-0"><?php echo $total_bookmarks; ?></h2>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-bookmark-fill fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="bookmarks.php" class="small text-primary text-decoration-none fw-semibold">
                        View Reading List <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">My Submissions</span>
                        <h2 class="fw-bold text-navy mb-0"><?php echo $total_submissions; ?></h2>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="my_submissions.php" class="small text-success text-decoration-none fw-semibold">
                        Check Status & History <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Subscribed Topics</span>
                        <h2 class="fw-bold text-navy mb-0"><?php echo $total_subscriptions; ?></h2>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-bell-fill fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="subscriptions.php" class="small text-warning-emphasis text-decoration-none fw-semibold">
                        Manage Subscriptions <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-rss-fill text-success me-2"></i> My Customized Feed
                    </h5>
                    <a href="subscriptions.php" class="small text-muted text-decoration-none">
                        <i class="bi bi-sliders me-1"></i> Edit Topics
                    </a>
                </div>

                <?php if (!empty($personalized_feed)): ?>
                    <div class="row g-3">
                        <?php foreach ($personalized_feed as $art): ?>
                            <div class="col-md-6">
                                <div class="news-card border rounded-3">
                                    <div class="news-card-img-wrapper" style="height: 150px;">
                                        <a href="../article.php?id=<?php echo $art['id']; ?>">
                                            <img src="<?php echo get_image_url($art['image']); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" class="news-card-img" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                                        </a>
                                        <span class="position-absolute top-0 start-0 m-2 badge <?php echo get_category_badge($art['category_name']); ?> text-uppercase small">
                                            <?php echo htmlspecialchars($art['category_name']); ?>
                                        </span>
                                    </div>
                                    <div class="p-3 d-flex flex-column flex-grow-1">
                                        <h6 class="fw-bold text-navy mb-2" style="font-size: 0.95rem; line-height: 1.3;">
                                            <a href="../article.php?id=<?php echo $art['id']; ?>" class="text-navy text-decoration-none">
                                                <?php echo htmlspecialchars($art['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="mt-auto text-muted small d-flex justify-content-between align-items-center pt-2 border-top">
                                            <span><i class="bi bi-clock"></i> <?php echo format_date($art['created_at']); ?></span>
                                            <a href="../article.php?id=<?php echo $art['id']; ?>" class="text-success text-decoration-none fw-semibold">Read &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bell-slash display-5 d-block mb-3 text-secondary"></i>
                        <h6>No customized articles yet.</h6>
                        <p class="small mb-3">You haven't subscribed to any categories or no stories were published in them yet.</p>
                        <a href="subscriptions.php" class="btn btn-nsbm btn-sm rounded-pill px-4">Subscribe to Categories</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i> Recent Submissions
                    </h6>
                    <a href="my_submissions.php" class="small text-muted text-decoration-none">All</a>
                </div>

                <?php if (!empty($recent_submissions)): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($recent_submissions as $sub): ?>
                            <li class="py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-light text-dark border small" style="font-size: 0.7rem;"><?php echo htmlspecialchars($sub['category_name']); ?></span>
                                    <span class="badge bg-<?php echo ($sub['status'] === 'approved') ? 'success' : (($sub['status'] === 'rejected') ? 'danger' : 'warning text-dark'); ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </div>
                                <div class="fw-semibold small text-navy text-truncate"><?php echo htmlspecialchars($sub['title']); ?></div>
                                <small class="text-muted"><i class="bi bi-calendar3"></i> <?php echo format_date($sub['submitted_at']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-3">You have not submitted any club stories or announcements yet.</p>
                    <a href="submit_article.php" class="btn btn-outline-success btn-sm w-100 rounded-pill">
                        Submit a Story Now
                    </a>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 account-shortcuts-card">
                <h6 class="fw-bold mb-3 pb-2 border-bottom account-shortcuts-title">
                    <i class="bi bi-gear-wide-connected text-success me-2"></i> Account Shortcuts
                </h6>
                <div class="d-flex flex-column gap-2">
                    <a href="profile.php" class="btn btn-shortcut text-start py-2">
                        <i class="bi bi-person-gear text-success me-2"></i> Edit Profile & Password
                    </a>
                    <a href="bookmarks.php" class="btn btn-shortcut text-start py-2">
                        <i class="bi bi-bookmark text-primary me-2"></i> View Saved Articles
                    </a>
                    <a href="subscriptions.php" class="btn btn-shortcut text-start py-2">
                        <i class="bi bi-bell text-warning me-2"></i> Notification Preferences
                    </a>
                    <a href="../logout.php" class="btn btn-shortcut-danger text-start py-2 mt-2">
                        <i class="bi bi-box-arrow-right me-2"></i> Log Out of Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

