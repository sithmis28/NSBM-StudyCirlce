<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    set_flash('danger', 'Please login to submit articles for approval.');
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$error = '';

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');
    $image_name = null;

    if (empty($title) || empty($content) || !$category_id) {
        $error = "Please fill in the title, category, and article content.";
    } else {

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_ext, $allowed_exts)) {
                $error = "Invalid image file format. Allowed formats: JPG, PNG, WEBP.";
            } else {
                $new_filename = 'submission_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $upload_dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    $image_name = $new_filename;
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("
                INSERT INTO article_submissions (user_id, category_id, title, content, image, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            if ($stmt->execute([$user['id'], $category_id, $title, $content, $image_name])) {
                set_flash('success', 'Your story has been submitted successfully for editorial review!');
                header("Location: my_submissions.php");
                exit;
            } else {
                $error = "Failed to submit story. Please try again.";
            }
        }
    }
}

$page_title = "Submit Campus Article";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Submit Story</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="bg-success-subtle text-success p-3 rounded-circle me-3">
                        <i class="bi bi-pencil-square fs-3"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-1">Submit Article for Publication</h3>
                        <p class="text-muted small mb-0">Share student club achievements, technical projects, or campus initiatives with the NSBM community.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="submit_article.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold text-navy small">Story Headline / Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="e.g. STEMUP Club Organizes CodeSprint Hackathon 2026" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        <div class="invalid-feedback">Please provide a descriptive title.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold text-navy small">Topic / Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Topic...</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please choose a category.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="image" class="form-label fw-semibold text-navy small">Cover Photo (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/png, image/jpeg, image/webp">
                            <small class="text-muted" style="font-size: 0.75rem;">Supported formats: JPG, PNG, WEBP (Max 5MB)</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label fw-semibold text-navy small">Article Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="10" placeholder="Write full details about the initiative, event outcomes, quotes, and participants..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        <div class="invalid-feedback">Please write the article content.</div>
                    </div>

                    <div class="alert alert-light border p-3 rounded-3 small text-muted mb-4">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-success me-1"></i> Editorial Guidelines</h6>
                        <ul class="mb-0 ps-3">
                            <li>All submissions are reviewed by NSBM NewsDesk administrators before being published.</li>
                            <li>Ensure information is accurate and respectful of university media guidelines.</li>
                            <li>You can track review feedback and approval status inside your <strong>My Submissions</strong> tab.</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="dashboard.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-nsbm px-5 shadow-sm">
                            <i class="bi bi-send-check-fill me-1"></i> Submit for Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

