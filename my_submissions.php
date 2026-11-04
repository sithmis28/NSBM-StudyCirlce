<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$user_id = $user['id'];

$stmt = $pdo->prepare("
    SELECT a.*, c.name AS category_name
    FROM article_submissions a
    JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.submitted_at DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();

$page_title = "My Submissions";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Submissions</li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-journal-text text-success me-2"></i> My Submitted Articles
            </h2>
            <p class="text-muted small mb-0">Track the editorial progress, approvals, and administrator feedback for your submissions.</p>
        </div>
        <div>
            <a href="submit_article.php" class="btn btn-nsbm rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Submit New Story
            </a>
        </div>
    </div>

    <?php if (!empty($submissions)): ?>
        <div class="row g-4 mb-5">
            <?php foreach ($submissions as $sub): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-3 gap-2">
                            <div>
                                <span class="badge bg-light text-dark border me-2"><?php echo htmlspecialchars($sub['category_name']); ?></span>
                                <h4 class="fw-bold text-navy d-inline align-middle"><?php echo htmlspecialchars($sub['title']); ?></h4>
                            </div>
                            <div>
                                <?php if ($sub['status'] === 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7">
                                        <i class="bi bi-check-circle-fill me-1"></i> Approved & Published
                                    </span>
                                <?php elseif ($sub['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-7">
                                        <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 fs-7">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending Editorial Review
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars(substr($sub['content'], 0, 300))); ?>...
                        </p>

                        <?php if (!empty($sub['admin_notes'])): ?>
                            <div class="alert alert-light border-start border-3 border-info p-3 rounded-2 small text-dark mb-3">
                                <strong><i class="bi bi-chat-left-quote text-info me-1"></i> Admin Feedback:</strong>
                                <?php echo htmlspecialchars($sub['admin_notes']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top text-muted small">
                            <span><i class="bi bi-clock me-1"></i> Submitted on: <?php echo format_date($sub['submitted_at'], true); ?></span>
                            <?php if ($sub['status'] === 'approved'): ?>
                                <span class="text-success fw-semibold"><i class="bi bi-globe me-1"></i> Live on Campus News</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-5">
            <i class="bi bi-pencil-square display-3 text-muted mb-3"></i>
            <h4 class="fw-bold text-navy">No story submissions found</h4>
            <p class="text-muted small mb-4">You have not submitted any club stories, events, or articles yet.</p>
            <a href="submit_article.php" class="btn btn-nsbm align-self-center rounded-pill px-4">
                Submit Your First Article
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

