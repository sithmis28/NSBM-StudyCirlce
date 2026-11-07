<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to access faculty categories and department news.');

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header("Location: news.php");
    exit;
}

$cat_stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$cat_stmt->execute([$slug]);
$category = $cat_stmt->fetch();

if (!$category) {
    header("Location: news.php");
    exit;
}

$page_title = $category['name'] . " News";

$user_id = is_logged_in() ? current_user()['id'] : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_sub') {
    if (!is_logged_in()) {
        header("Location: login.php?redirect=" . urlencode("category.php?slug=" . $slug));
        exit;
    }

    $is_sub = is_subscribed($pdo, $user_id, $category['id']);
    if ($is_sub) {
        $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?")->execute([$user_id, $category['id']]);
        set_flash('info', "You unsubscribed from {$category['name']} updates.");
    } else {
        $pdo->prepare("INSERT INTO subscriptions (user_id, category_id) VALUES (?, ?)")->execute([$user_id, $category['id']]);
        set_flash('success', "You subscribed to {$category['name']}! You will receive feed alerts for this category.");
    }
    header("Location: category.php?slug=" . $slug);
    exit;
}

$subscribed = is_subscribed($pdo, $user_id, $category['id']);

try {
    $articles_stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name,
               (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id AND cm.status = 'approved') AS comment_count
        FROM news n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.category_id = ? AND n.status = 'published'
        ORDER BY n.created_at DESC
    ");
    $articles_stmt->execute([$category['id']]);
    $articles = $articles_stmt->fetchAll();
} catch (Exception $e) {
    $articles = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="news.php" class="text-decoration-none">Categories</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="p-4 p-md-5 rounded-4 mb-5 border shadow-sm" style="background: linear-gradient(135deg, #2563A6 0%, #2E7D5B 100%); color: white;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge <?php echo get_category_badge($category['name']); ?> text-uppercase px-3 py-2 mb-2">
                    Faculty & Department
                </span>
                <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="text-light-subtle mb-0" style="max-width: 680px;">
                    <?php echo htmlspecialchars($category['description']); ?>
                </p>
            </div>
            <div>
                <form method="POST">
                    <input type="hidden" name="action" value="toggle_sub">
                    <?php if ($subscribed): ?>
                        <button type="submit" class="btn btn-outline-light rounded-pill px-4">
                            <i class="bi bi-bell-slash-fill me-1"></i> Subscribed
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-nsbm rounded-pill px-4 shadow">
                            <i class="bi bi-bell-fill me-1"></i> Subscribe to Topic
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $art): ?>
                <?php $bookmarked = is_bookmarked($pdo, $user_id, $art['id']); ?>
                <div class="col-md-6 col-lg-4">
                    <div class="news-card">
                        <div class="news-card-img-wrapper">
                            <a href="article.php?id=<?php echo $art['id']; ?>">
                                <img src="<?php echo get_image_url($art['image']); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" class="news-card-img" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                            </a>
                        </div>
                        <div class="news-card-body">
                            <h5 class="news-card-title">
                                <a href="article.php?id=<?php echo $art['id']; ?>">
                                    <?php echo htmlspecialchars($art['title']); ?>
                                </a>
                            </h5>
                            <p class="news-card-excerpt">
                                <?php echo htmlspecialchars($art['excerpt'] ?: substr(strip_tags($art['content']), 0, 110) . '...'); ?>
                            </p>
                            <div class="news-card-footer">
                                <div class="d-flex align-items-center gap-2">
                                    <span><i class="bi bi-clock me-1"></i> <?php echo format_date($art['created_at']); ?></span>
                                    <span>&bull;</span>
                                    <span><i class="bi bi-eye me-1"></i> <?php echo number_format($art['views']); ?></span>
                                </div>
                                <button class="bookmark-btn bookmark-toggle-btn <?php echo $bookmarked ? 'active' : ''; ?>" data-news-id="<?php echo $art['id']; ?>" title="Save article">
                                    <i class="bi <?php echo $bookmarked ? 'bi-bookmark-fill text-danger' : 'bi-bookmark'; ?>"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-light border text-center py-5 rounded-4 shadow-sm">
                    <i class="bi bi-journal-x display-4 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">No articles currently in this category</h5>
                    <p class="text-muted small">Stay tuned! New publications and stories are added regularly.</p>
                    <a href="news.php" class="btn btn-nsbm btn-sm rounded-pill px-4">Browse All News</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

