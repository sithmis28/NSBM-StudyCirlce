<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to access all campus news stories.');

$page_title = "All Campus News";
require_once __DIR__ . '/includes/header.php';

$search_query = trim($_GET['q'] ?? '');
$category_filter = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$date_filter = trim($_GET['date'] ?? '');
$sort_by = $_GET['sort'] ?? 'newest';
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$per_page = 6;
$offset = ($page - 1) * $per_page;

$conditions = ["n.status = 'published'"];
$params = [];

if (!empty($search_query)) {
    $conditions[] = "(n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
    $like = "%{$search_query}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($category_filter) {
    $conditions[] = "n.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($date_filter)) {
    $conditions[] = "DATE(n.created_at) = ?";
    $params[] = $date_filter;
}

$where_clause = implode(" AND ", $conditions);

$order_clause = "n.created_at DESC";
if ($sort_by === 'popular') {
    $order_clause = "n.views DESC, n.created_at DESC";
} elseif ($sort_by === 'oldest') {
    $order_clause = "n.created_at ASC";
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM news n WHERE {$where_clause}");
$count_stmt->execute($params);
$total_articles = $count_stmt->fetchColumn();
$total_pages = ceil($total_articles / $per_page);

$query_sql = "
    SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name,
           (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id AND cm.status = 'approved') AS comment_count
    FROM news n
    LEFT JOIN categories c ON n.category_id = c.id
    LEFT JOIN users u ON n.author_id = u.id
    WHERE {$where_clause}
    ORDER BY {$order_clause}
    LIMIT {$per_page} OFFSET {$offset}
";
$stmt = $pdo->prepare($query_sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$user_id = is_logged_in() ? current_user()['id'] : null;
?>

<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-newspaper text-success me-2"></i> Campus News & Updates
            </h2>
            <p class="text-muted small mb-0">Browse recent publications, academic reports, and campus event coverage.</p>
        </div>
        <div class="text-muted small">
            Showing <strong><?php echo count($articles); ?></strong> of <strong><?php echo $total_articles; ?></strong> articles
        </div>
    </div>

    <div class="bg-white p-3 p-md-4 rounded-3 border shadow-sm mb-4">
        <form method="GET" action="news.php" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-navy">Search by keywords:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-success"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search title or content..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-navy">Category:</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($nav_categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-navy">Filter by Date:</label>
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-navy">Sort by:</label>
                <select name="sort" class="form-select">
                    <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                    <option value="popular" <?php echo ($sort_by === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                    <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-nsbm w-100" title="Apply Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <?php if (!empty($search_query) || $category_filter || !empty($date_filter) || $sort_by !== 'newest'): ?>
                    <a href="news.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>
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
                            <span class="position-absolute top-0 start-0 m-3 badge <?php echo get_category_badge($art['category_name']); ?> text-uppercase">
                                <?php echo htmlspecialchars($art['category_name'] ?? 'General'); ?>
                            </span>
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
                                    <span><i class="bi bi-calendar3 me-1"></i> <?php echo format_date($art['created_at']); ?></span>
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
                    <i class="bi bi-search display-4 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">No matching articles found</h5>
                    <p class="text-muted small">Try adjusting your search criteria or removing filters.</p>
                    <a href="news.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Clear All Filters</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

