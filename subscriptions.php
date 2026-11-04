<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = (int)$_POST['category_id'];
    $check = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND category_id = ?");
    $check->execute([$user_id, $category_id]);
    $existing = $check->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?");
        $del->execute([$user_id, $category_id]);
        set_flash('info', 'Subscription preferences updated.');
    } else {
        $ins = $pdo->prepare("INSERT INTO subscriptions (user_id, category_id) VALUES (?, ?)");
        $ins->execute([$user_id, $category_id]);
        set_flash('success', 'Subscribed to topic notifications.');
    }
    header("Location: subscriptions.php");
    exit;
}

$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories = $categories_stmt->fetchAll();

$user_subs_stmt = $pdo->prepare("SELECT category_id FROM subscriptions WHERE user_id = ?");
$user_subs_stmt->execute([$user_id]);
$subscribed_category_ids = $user_subs_stmt->fetchAll(PDO::FETCH_COLUMN);

$page_title = "Category Subscriptions";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-bell-fill text-warning me-2"></i> Category Subscriptions
            </h2>
            <p class="text-muted small mb-0">Follow your preferred academic faculties, sports departments, and student societies for personalized feeds.</p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7">
            Subscribed to <?php echo count($subscribed_category_ids); ?> Categories
        </span>
    </div>

    <div class="row g-4 mb-5">
        <?php foreach ($all_categories as $cat): ?>
            <?php $is_sub = in_array($cat['id'], $subscribed_category_ids); ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge <?php echo get_category_badge($cat['name']); ?> text-uppercase px-3 py-2">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </span>
                            <?php if ($is_sub): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold text-navy mb-2"><?php echo htmlspecialchars($cat['name']); ?></h5>
                        <p class="text-muted small mb-4" style="line-height: 1.5;">
                            <?php echo htmlspecialchars($cat['description']); ?>
                        </p>
                    </div>

                    <form method="POST" action="subscriptions.php">
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <?php if ($is_sub): ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill py-2">
                                <i class="bi bi-bell-slash me-1"></i> Unsubscribe
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-nsbm btn-sm w-100 rounded-pill py-2 shadow-sm">
                                <i class="bi bi-bell-fill me-1"></i> Follow Topic
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

