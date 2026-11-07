<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to use campus search.');

$page_title = "Search Results";
require_once __DIR__ . '/includes/header.php';

$query = trim($_GET['q'] ?? '');
$user_id = is_logged_in() ? current_user()['id'] : null;

$news_results = [];
$ann_results = [];

if (!empty($query)) {

    $like = "%{$query}%";
    $news_stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name,
               (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id AND cm.status = 'approved') AS comment_count
        FROM news n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.status = 'published' AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)
        ORDER BY n.created_at DESC
    ");
    $news_stmt->execute([$like, $like, $like]);
    $news_results = $news_stmt->fetchAll();

    $ann_stmt = $pdo->prepare("
        SELECT * FROM announcements
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC
    ");
    $ann_stmt->execute([$like, $like]);
    $ann_results = $ann_stmt->fetchAll();
}
?>

<div class="container py-4">

    <div class="p-4 bg-white rounded-4 border shadow-sm mb-5">
        <form action="search.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-success fs-5"></i></span>
                    <input type="text" name="q" class="form-control form-control-lg border-start-0 ps-0" placeholder="Search news, topics, events, announcements..." value="<?php echo htmlspecialchars($query); ?>" autofocus required>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-nsbm btn-lg w-100">Search</button>
            </div>
        </form>
    </div>

    <div class="mb-4 pb-2 border-bottom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-navy mb-0">
            Search Results for: <span class="text-success">"<?php echo htmlspecialchars($query); ?>"</span>
        </h4>
        <span class="badge bg-light text-dark border fs-6">
            <?php echo count($news_results) + count($ann_results); ?> Results Found
        </span>
    </div>

    <?php if (empty($query)): ?>
        <div class="alert alert-info py-5 text-center rounded-4">
            <i class="bi bi-search display-5 d-block mb-3"></i>
            <h5>Enter a search term above</h5>
            <p class="text-muted small">Search across all campus news articles, research updates, faculty announcements, and student circulars.</p>
        </div>
    <?php elseif (empty($news_results) && empty($ann_results)): ?>
        <div class="alert alert-light border py-5 text-center rounded-4 shadow-sm">
            <i class="bi bi-emoji-neutral display-5 text-muted d-block mb-3"></i>
            <h5>No matches found for "<?php echo htmlspecialchars($query); ?>"</h5>
            <p class="text-muted small">Please try different keywords, broader search terms, or check spelling.</p>
            <a href="news.php" class="btn btn-nsbm btn-sm rounded-pill px-4">Browse All News</a>
        </div>
    <?php else: ?>

        <?php if (!empty($news_results)): ?>
            <h5 class="fw-bold text-navy mb-3">
                <i class="bi bi-newspaper text-success me-2"></i> News Articles (<?php echo count($news_results); ?>)
            </h5>
            <div class="row g-4 mb-5">
                <?php foreach ($news_results as $item): ?>
                    <?php $bookmarked = is_bookmarked($pdo, $user_id, $item['id']); ?>
                    <div class="col-md-6 col-lg-4">
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
                                    <span><i class="bi bi-calendar3 me-1"></i> <?php echo format_date($item['created_at']); ?></span>
                                    <button class="bookmark-btn bookmark-toggle-btn <?php echo $bookmarked ? 'active' : ''; ?>" data-news-id="<?php echo $item['id']; ?>" title="Save article">
                                        <i class="bi <?php echo $bookmarked ? 'bi-bookmark-fill text-danger' : 'bi-bookmark'; ?>"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($ann_results)): ?>
            <h5 class="fw-bold text-navy mb-3">
                <i class="bi bi-megaphone text-danger me-2"></i> Official Circulars & Notices (<?php echo count($ann_results); ?>)
            </h5>
            <div class="row g-3 mb-5">
                <?php foreach ($ann_results as $ann): ?>
                    <div class="col-12">
                        <div class="card p-3 rounded-3 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-<?php echo ($ann['priority'] === 'urgent') ? 'danger' : (($ann['priority'] === 'important') ? 'warning text-dark' : 'success'); ?>">
                                    <?php echo strtoupper($ann['priority']); ?>
                                </span>
                                <small class="text-muted"><i class="bi bi-clock"></i> <?php echo format_date($ann['created_at']); ?></small>
                            </div>
                            <h6 class="fw-bold text-navy mb-1"><?php echo htmlspecialchars($ann['title']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($ann['content']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

