<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to read full campus articles.');

$news_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$news_id) {
    header("Location: news.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_comment') {
    if (!is_logged_in()) {
        set_flash('danger', 'You must be logged in to post comments.');
        header("Location: login.php?redirect=" . urlencode("article.php?id=" . $news_id));
        exit;
    }

    $comment_text = trim($_POST['comment'] ?? '');
    if (empty($comment_text)) {
        set_flash('danger', 'Comment cannot be blank.');
    } else {
        $user_id = current_user()['id'];
        $comment_stmt = $pdo->prepare("INSERT INTO comments (news_id, user_id, comment, status) VALUES (?, ?, ?, 'approved')");
        $comment_stmt->execute([$news_id, $user_id, $comment_text]);
        set_flash('success', 'Your comment has been posted successfully!');
    }
    header("Location: article.php?id=" . $news_id . "#comments");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.faculty AS author_faculty
        FROM news n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.id = ? AND n.status = 'published'
    ");
    $stmt->execute([$news_id]);
    $article = $stmt->fetch();

    if (!$article) {
        header("Location: news.php");
        exit;
    }

    $pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$news_id]);
    $article['views'] += 1;

} catch (Exception $e) {
    header("Location: news.php");
    exit;
}

try {
    $comments_stmt = $pdo->prepare("
        SELECT c.*, u.name AS user_name, u.faculty AS user_faculty, u.role AS user_role
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.news_id = ? AND c.status = 'approved'
        ORDER BY c.created_at DESC
    ");
    $comments_stmt->execute([$news_id]);
    $comments = $comments_stmt->fetchAll();
} catch (Exception $e) {
    $comments = [];
}

try {
    $related_stmt = $pdo->prepare("
        SELECT id, title, image, created_at
        FROM news
        WHERE category_id = ? AND id != ? AND status = 'published'
        ORDER BY created_at DESC
        LIMIT 4
    ");
    $related_stmt->execute([$article['category_id'], $news_id]);
    $related_articles = $related_stmt->fetchAll();
} catch (Exception $e) {
    $related_articles = [];
}

$page_title = $article['title'];
$user_id = is_logged_in() ? current_user()['id'] : null;
$bookmarked = is_bookmarked($pdo, $user_id, $article['id']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="news.php" class="text-decoration-none">News</a></li>
            <?php if (!empty($article['category_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="category.php?slug=<?php echo urlencode($article['category_slug']); ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($article['category_name']); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">
                <?php echo htmlspecialchars($article['title']); ?>
            </li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="row g-4">

        <div class="col-lg-8">
            <article class="bg-white p-4 p-md-5 rounded-4 border shadow-sm mb-5">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="category.php?slug=<?php echo urlencode($article['category_slug']); ?>" class="badge <?php echo get_category_badge($article['category_name']); ?> text-decoration-none text-uppercase px-3 py-2">
                        <?php echo htmlspecialchars($article['category_name'] ?? 'General'); ?>
                    </a>

                    <button class="bookmark-btn bookmark-toggle-btn <?php echo $bookmarked ? 'active' : ''; ?>" data-news-id="<?php echo $article['id']; ?>" title="Save article">
                        <i class="bi <?php echo $bookmarked ? 'bi-bookmark-fill text-danger' : 'bi-bookmark'; ?> fs-5"></i>
                    </button>
                </div>

                <h1 class="fw-bold text-navy mb-3" style="font-size: 2.2rem; line-height: 1.25;">
                    <?php echo htmlspecialchars($article['title']); ?>
                </h1>

                <div class="d-flex flex-wrap align-items-center justify-content-between text-muted small py-3 border-top border-bottom mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-5 me-2 text-success"></i>
                            <div>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($article['author_name'] ?? 'NSBM NewsDesk'); ?></span>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($article['author_faculty'] ?? 'Editorial Board'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
                        <span><i class="bi bi-calendar3 me-1"></i> <?php echo format_date($article['created_at']); ?></span>
                        <span><i class="bi bi-eye me-1"></i> <?php echo number_format($article['views']); ?> views</span>
                    </div>
                </div>

                <?php if (!empty($article['image'])): ?>
                    <div class="rounded-3 overflow-hidden mb-4 shadow-sm" style="max-height: 480px;">
                        <img src="<?php echo get_image_url($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                    </div>
                <?php endif; ?>

                <div class="article-body fs-6 lh-lg mb-5 text-dark" style="letter-spacing: 0.2px;">
                    <?php echo $article['content']; ?>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-light rounded-3 border">
                    <span class="fw-bold text-navy small mb-2 mb-sm-0">
                        <i class="bi bi-share me-1 text-success"></i> Share this story:
                    </span>
                    <div class="d-flex gap-2">
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['title']); ?>&url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            <i class="bi bi-twitter-x"></i> Share
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-facebook"></i> Post
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-linkedin"></i> Share
                        </a>
                    </div>
                </div>
            </article>

            <section id="comments" class="bg-white p-4 p-md-5 rounded-4 border shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h4 class="fw-bold text-navy mb-0">
                        <i class="bi bi-chat-dots-fill text-success me-2"></i> Discussion & Reader Feedback (<?php echo count($comments); ?>)
                    </h4>
                </div>

                <?php if (is_logged_in()): ?>
                    <form method="POST" action="article.php?id=<?php echo $news_id; ?>" class="mb-5">
                        <input type="hidden" name="action" value="post_comment">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy small">Leave your thoughts as <strong><?php echo htmlspecialchars(current_user()['name']); ?></strong>:</label>
                            <textarea name="comment" rows="3" class="form-control" placeholder="Share your perspective, question or congratulatory note..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-nsbm btn-sm rounded-pill px-4">
                                <i class="bi bi-send-fill me-1"></i> Post Comment
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border p-4 text-center rounded-3 mb-5">
                        <i class="bi bi-person-lock fs-3 text-muted d-block mb-2"></i>
                        <h6 class="fw-bold mb-1">Join the campus conversation</h6>
                        <p class="text-muted small mb-3">Please sign in with your NSBM student or staff account to post comments.</p>
                        <a href="login.php?redirect=<?php echo urlencode('article.php?id=' . $news_id); ?>" class="btn btn-nsbm btn-sm rounded-pill px-4">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Comment
                        </a>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $com): ?>
                            <div class="comment-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle fs-4 text-success me-2"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark fs-7"><?php echo htmlspecialchars($com['user_name']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($com['user_faculty'] ?? 'NSBM Community'); ?></small>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo time_ago($com['created_at']); ?></small>
                                </div>
                                <p class="mb-0 text-dark small" style="line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($com['comment'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-chat-square-text fs-3 d-block mb-2 text-secondary"></i>
                            <p class="small mb-0">No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-4">

            <div class="sidebar-box text-center">
                <img src="assets/images/logo_nsbm.png" alt="NSBM" style="height: 48px;" class="mb-3">
                <h6 class="fw-bold text-navy mb-1"><?php echo htmlspecialchars($article['author_name'] ?? 'NSBM NewsDesk'); ?></h6>
                <p class="text-muted small mb-3">Official Media & Public Relations Unit, NSBM Green University Town.</p>
                <a href="news.php" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                    <i class="bi bi-collection me-1"></i> More from this publisher
                </a>
            </div>

            <?php if (!empty($related_articles)): ?>
                <div class="sidebar-box">
                    <h5 class="sidebar-title">
                        <i class="bi bi-bookmarks-fill text-success me-2"></i> Related Stories
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($related_articles as $rel): ?>
                            <div class="d-flex align-items-center gap-3">
                                <a href="article.php?id=<?php echo $rel['id']; ?>" class="flex-shrink-0 rounded-2 overflow-hidden" style="width: 80px; height: 60px;">
                                    <img src="<?php echo get_image_url($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                                </a>
                                <div>
                                    <h6 class="mb-1" style="font-size: 0.88rem; line-height: 1.3;">
                                        <a href="article.php?id=<?php echo $rel['id']; ?>" class="text-navy text-decoration-none fw-semibold">
                                            <?php echo htmlspecialchars($rel['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted"><i class="bi bi-clock"></i> <?php echo format_date($rel['created_at']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="sidebar-box">
                <h5 class="sidebar-title">
                    <i class="bi bi-folder-symlink me-2 text-warning"></i> Explore Faculties
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="py-2 border-bottom"><a href="category.php?slug=computing-tech" class="text-decoration-none text-dark d-flex justify-content-between"><span><i class="bi bi-laptop me-2 text-success"></i> Computing & Technology</span> <i class="bi bi-arrow-right text-muted"></i></a></li>
                    <li class="py-2 border-bottom"><a href="category.php?slug=academic" class="text-decoration-none text-dark d-flex justify-content-between"><span><i class="bi bi-briefcase me-2 text-primary"></i> Business & Management</span> <i class="bi bi-arrow-right text-muted"></i></a></li>
                    <li class="py-2 border-bottom"><a href="category.php?slug=events-festivals" class="text-decoration-none text-dark d-flex justify-content-between"><span><i class="bi bi-calendar-check me-2 text-warning"></i> Campus Events & Galas</span> <i class="bi bi-arrow-right text-muted"></i></a></li>
                    <li class="py-2"><a href="category.php?slug=sports-fitness" class="text-decoration-none text-dark d-flex justify-content-between"><span><i class="bi bi-trophy me-2 text-danger"></i> Sports & Athletic Clubs</span> <i class="bi bi-arrow-right text-muted"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

