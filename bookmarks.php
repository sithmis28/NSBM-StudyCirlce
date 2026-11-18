<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$user_id = $user['id'];

if (isset($_GET['remove']) && filter_input(INPUT_GET, 'remove', FILTER_VALIDATE_INT)) {
    $remove_id = (int)$_GET['remove'];
    $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND news_id = ?");
    $del->execute([$user_id, $remove_id]);
    set_flash('info', 'Article removed from saved reading list.');
    header("Location: bookmarks.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, b.created_at AS bookmarked_at
    FROM bookmarks b
    JOIN news n ON b.news_id = n.id
    LEFT JOIN categories c ON n.category_id = c.id
    LEFT JOIN users u ON n.author_id = u.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookmarks = $stmt->fetchAll();

$page_title = "Saved Reading List";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bookmarks</li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-bookmark-fill text-danger me-2"></i> Saved Reading List
            </h2>
            <p class="text-muted small mb-0">Articles and announcements you have bookmarked for offline review or later reading.</p>
        </div>
        <span class="badge bg-light text-dark border fs-6">
            <?php echo count($bookmarks); ?> Bookmarked Articles
        </span>
    </div>

    <?php if (!empty($bookmarks)): ?>
        <div class="row g-4 mb-5">
            <?php foreach ($bookmarks as $art): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="news-card">
                        <div class="news-card-img-wrapper">
                            <a href="../article.php?id=<?php echo $art['id']; ?>">
                                <img src="<?php echo get_image_url($art['image']); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" class="news-card-img" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                            </a>
                            <span class="position-absolute top-0 start-0 m-3 badge <?php echo get_category_badge($art['category_name']); ?> text-uppercase">
                                <?php echo htmlspecialchars($art['category_name']); ?>
                            </span>
                        </div>
                        <div class="news-card-body">
                            <h5 class="news-card-title">
                                <a href="../article.php?id=<?php echo $art['id']; ?>">
                                    <?php echo htmlspecialchars($art['title']); ?>
                                </a>
                            </h5>
                            <p class="news-card-excerpt">
                                <?php echo htmlspecialchars($art['excerpt'] ?: substr(strip_tags($art['content']), 0, 110) . '...'); ?>
                            </p>
                            <div class="news-card-footer">
                                <span class="text-muted"><i class="bi bi-clock"></i> Saved <?php echo time_ago($art['bookmarked_at']); ?></span>
                                <a href="bookmarks.php?remove=<?php echo $art['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Remove this article from bookmarks?');">
                                    <i class="bi bi-trash3 me-1"></i> Remove
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-5">
            <i class="bi bi-bookmark-dash display-3 text-muted mb-3"></i>
            <h4 class="fw-bold text-navy">Your reading list is empty</h4>
            <p class="text-muted small mb-4">When reading articles on NSBM NewsDesk, tap the bookmark icon to save them here.</p>
            <a href="../news.php" class="btn btn-nsbm align-self-center rounded-pill px-4">
                Explore Campus News
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

