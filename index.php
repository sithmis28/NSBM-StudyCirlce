<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login('Please sign in with your NSBM account to browse the campus news feed.');

$page_title = "Campus News Feed";
require_once __DIR__ . '/includes/header.php';

try {
    $breaking_stmt = $pdo->prepare("SELECT id, title FROM news WHERE is_breaking = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    $breaking_stmt->execute();
    $breaking_news = $breaking_stmt->fetchAll();
} catch (Exception $e) {
    $breaking_news = [];
}

try {
    $featured_stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM news n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.is_featured = 1 AND n.status = 'published'
        ORDER BY n.created_at DESC
        LIMIT 3
    ");
    $featured_stmt->execute();
    $featured_news = $featured_stmt->fetchAll();

    $main_featured = $featured_news[0] ?? null;
    $side_featured = array_slice($featured_news, 1, 2);
} catch (Exception $e) {
    $main_featured = null;
    $side_featured = [];
}

try {
    $latest_sql = "
        SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name,
               (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id AND cm.status = 'approved') AS comment_count
        FROM news n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.status = 'published'
        ORDER BY n.created_at DESC
        LIMIT 6
    ";
    $latest_stmt = $pdo->query($latest_sql);
    $latest_news = $latest_stmt->fetchAll();
} catch (Exception $e) {
    $latest_news = [];
}

try {
    $announcements_stmt = $pdo->query("SELECT * FROM announcements ORDER BY FIELD(priority, 'urgent', 'important', 'normal'), created_at DESC LIMIT 4");
    $announcements = $announcements_stmt->fetchAll();
} catch (Exception $e) {
    $announcements = [];
}

$user_id = is_logged_in() ? current_user()['id'] : null;
?>

<?php if (!empty($breaking_news)): ?>
<div class="breaking-news-bar">
    <div class="container">
        <div class="d-flex align-items-center">
            <span class="breaking-badge me-3 d-flex align-items-center">
                <i class="bi bi-lightning-charge-fill me-1"></i> Breaking
            </span>
            <div class="overflow-hidden flex-grow-1">
                <?php foreach ($breaking_news as $bn): ?>
                    <a href="article.php?id=<?php echo $bn['id']; ?>" class="breaking-ticker-link me-4 text-truncate d-inline-block" style="max-width: 90%;">
                        <i class="bi bi-dot text-danger"></i> <?php echo htmlspecialchars($bn['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container py-4">

    <?php display_flash(); ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center bg-success-subtle text-success-emphasis border border-success-subtle rounded-3 p-3 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success text-white"><i class="bi bi-stars me-1"></i> Showcase</span>
            <span class="small fw-semibold">Experience the official NSBM NewsDesk Interactive Landing Page & Overview</span>
        </div>
        <a href="landing.php" class="btn btn-sm btn-nsbm rounded-pill px-3 mt-2 mt-sm-0 text-white text-decoration-none">
            View Landing Page &rarr;
        </a>
    </div>

    <?php if ($main_featured): ?>
    <section class="mb-5">
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="hero-card">
                    <img src="<?php echo get_image_url($main_featured['image']); ?>" alt="<?php echo htmlspecialchars($main_featured['title']); ?>" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                    <div class="hero-card-overlay">
                        <span class="badge <?php echo get_category_badge($main_featured['category_name']); ?> mb-2 px-3 py-2 text-uppercase fs-7">
                            <?php echo htmlspecialchars($main_featured['category_name'] ?? 'University'); ?>
                        </span>
                        <h2 class="hero-card-title">
                            <a href="article.php?id=<?php echo $main_featured['id']; ?>">
                                <?php echo htmlspecialchars($main_featured['title']); ?>
                            </a>
                        </h2>
                        <div class="d-flex align-items-center small text-light gap-3">
                            <span><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($main_featured['author_name'] ?? 'NSBM Media'); ?></span>
                            <span><i class="bi bi-clock me-1"></i> <?php echo time_ago($main_featured['created_at']); ?></span>
                            <span><i class="bi bi-eye me-1"></i> <?php echo number_format($main_featured['views']); ?> views</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if (!empty($side_featured)): ?>
                    <?php foreach ($side_featured as $sf): ?>
                        <div class="sub-featured-card">
                            <img src="<?php echo get_image_url($sf['image']); ?>" alt="<?php echo htmlspecialchars($sf['title']); ?>" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                            <div class="sub-featured-overlay">
                                <span class="badge <?php echo get_category_badge($sf['category_name']); ?> mb-1 text-uppercase">
                                    <?php echo htmlspecialchars($sf['category_name'] ?? 'Campus'); ?>
                                </span>
                                <h3 class="sub-featured-title">
                                    <a href="article.php?id=<?php echo $sf['id']; ?>">
                                        <?php echo htmlspecialchars($sf['title']); ?>
                                    </a>
                                </h3>
                                <div class="small text-light-subtle d-flex align-items-center gap-2">
                                    <small><i class="bi bi-clock me-1"></i> <?php echo time_ago($sf['created_at']); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 bg-white rounded-3 border h-100 d-flex flex-column justify-content-center text-center">
                        <i class="bi bi-newspaper display-4 text-success mb-3"></i>
                        <h5 class="fw-bold">Stay Updated with NSBM</h5>
                        <p class="text-muted small">Explore timely news, official student circulars, and departmental innovations.</p>
                        <a href="news.php" class="btn btn-nsbm btn-sm align-self-center">Browse All Articles</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h4 class="fw-bold text-navy mb-0">
                    <i class="bi bi-collection text-success me-2"></i> Latest Campus News
                </h4>
                <a href="news.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php if (!empty($latest_news)): ?>
                    <?php foreach ($latest_news as $item): ?>
                        <?php $bookmarked = is_bookmarked($pdo, $user_id, $item['id']); ?>
                        <div class="col-md-6">
                            <div class="news-card">
                                <div class="news-card-img-wrapper">
                                    <a href="article.php?id=<?php echo $item['id']; ?>">
                                        <img src="<?php echo get_image_url($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-card-img" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                                    </a>
                                    <span class="position-absolute top-0 start-0 m-3 badge <?php echo get_category_badge($item['category_name']); ?> text-uppercase">
                                        <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
                                    </span>
                                </div>
                                <div class="news-card-body">
                                    <h5 class="news-card-title">
                                        <a href="article.php?id=<?php echo $item['id']; ?>">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="news-card-excerpt">
                                        <?php echo htmlspecialchars($item['excerpt'] ?: substr(strip_tags($item['content']), 0, 110) . '...'); ?>
                                    </p>
                                    <div class="news-card-footer">
                                        <div class="d-flex align-items-center gap-2">
                                            <span><i class="bi bi-clock me-1"></i> <?php echo format_date($item['created_at']); ?></span>
                                            <span>&bull;</span>
                                            <span><i class="bi bi-chat-text me-1"></i> <?php echo $item['comment_count']; ?></span>
                                        </div>
                                        <button class="bookmark-btn bookmark-toggle-btn <?php echo $bookmarked ? 'active' : ''; ?>" data-news-id="<?php echo $item['id']; ?>" title="Save article">
                                            <i class="bi <?php echo $bookmarked ? 'bi-bookmark-fill text-danger' : 'bi-bookmark'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-info-circle display-5 d-block mb-3"></i>
                            <h5>No news articles published yet.</h5>
                            <p class="text-muted">Check back later or visit the admin panel to add stories.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="sidebar-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="sidebar-title mb-0">
                        <i class="bi bi-megaphone-fill text-danger me-2"></i> Official Notices
                    </h5>
                    <a href="announcements.php" class="small text-muted text-decoration-none">More</a>
                </div>
                <div class="announcements-list">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="announcement-item priority-<?php echo htmlspecialchars($ann['priority']); ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-<?php echo ($ann['priority'] == 'urgent') ? 'danger' : (($ann['priority'] == 'important') ? 'warning text-dark' : 'success'); ?> small" style="font-size: 0.65rem;">
                                        <?php echo strtoupper($ann['priority']); ?>
                                    </span>
                                    <small class="text-muted"><i class="bi bi-clock"></i> <?php echo format_date($ann['created_at']); ?></small>
                                </div>
                                <div class="announcement-title">
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </div>
                                <p class="small text-muted mb-0">
                                    <?php echo htmlspecialchars(substr($ann['content'], 0, 100)); ?>...
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">No active announcements at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-box">
                <h5 class="sidebar-title">
                    <i class="bi bi-tags-fill text-success me-2"></i> News Topics
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($nav_categories as $c): ?>
                        <a href="category.php?slug=<?php echo urlencode($c['slug']); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-box text-center bg-light border-success-subtle p-4" style="background: linear-gradient(135deg, #ffffff 0%, #e6f4ea 100%);">
                <i class="bi bi-lightbulb-fill text-warning display-5 mb-2"></i>
                <h5 class="fw-bold text-navy">Got Campus News to Share?</h5>
                <p class="text-muted small mb-3">
                    Are you a club leader, society president, or student innovator? Submit your event or story for review by the NSBM NewsDesk editorial board!
                </p>
                <?php if (is_logged_in()): ?>
                    <a href="user/submit_article.php" class="btn btn-nsbm w-100 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Submit Your Article
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=user/submit_article.php" class="btn btn-nsbm w-100 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login to Submit
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
