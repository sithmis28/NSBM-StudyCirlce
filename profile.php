<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$user = current_user();
$user_id = $user['id'];
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');

    if (empty($name) || empty($faculty)) {
        $error = "Name and Faculty fields cannot be empty.";
    } else {
        $upd = $pdo->prepare("UPDATE users SET name = ?, student_id = ?, faculty = ? WHERE id = ?");
        if ($upd->execute([$name, $student_id, $faculty, $user_id])) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_faculty'] = $faculty;
            $_SESSION['user_student_id'] = $student_id;
            set_flash('success', 'Profile information updated successfully!');
            header("Location: profile.php");
            exit;
        } else {
            $error = "Failed to update profile.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    $pass_valid = password_verify($old_pass, $current_data['password']) || (hash('sha256', $old_pass) === $current_data['password']);

    if (!$pass_valid) {
        $error = "Current password does not match our records.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
        set_flash('success', 'Password changed successfully!');
        header("Location: profile.php");
        exit;
    }
}

$page_title = "User Profile Settings";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
        </ol>
    </nav>

    <?php display_flash(); ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-5">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                <h4 class="fw-bold text-navy mb-1"><i class="bi bi-person-bounding-box text-success me-2"></i> Profile Information</h4>
                <p class="text-muted small mb-4">Update your name, department faculty, and batch identifier.</p>

                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-navy">Registered Email (Cannot be changed)</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($current_data['email']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-navy">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($current_data['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="student_id" class="form-label small fw-semibold text-navy">Student ID / Batch Reg No.</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($current_data['student_id'] ?? ''); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="faculty" class="form-label small fw-semibold text-navy">Faculty</label>
                        <select class="form-select" id="faculty" name="faculty" required>
                            <option value="Faculty of Computing" <?php echo ($current_data['faculty'] === 'Faculty of Computing') ? 'selected' : ''; ?>>Faculty of Computing</option>
                            <option value="Faculty of Business" <?php echo ($current_data['faculty'] === 'Faculty of Business') ? 'selected' : ''; ?>>Faculty of Business</option>
                            <option value="Faculty of Engineering" <?php echo ($current_data['faculty'] === 'Faculty of Engineering') ? 'selected' : ''; ?>>Faculty of Engineering</option>
                            <option value="Faculty of Science" <?php echo ($current_data['faculty'] === 'Faculty of Science') ? 'selected' : ''; ?>>Faculty of Science</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-nsbm w-100 py-2 shadow-sm">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                <h4 class="fw-bold text-navy mb-1"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Security & Password</h4>
                <p class="text-muted small mb-4">Protect your account by creating a strong password.</p>

                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="old_password" class="form-label small fw-semibold text-navy">Current Password</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-semibold text-navy">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                        <small class="text-muted" style="font-size: 0.75rem;">Minimum 6 characters.</small>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label small fw-semibold text-navy">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-outline-danger w-100 py-2">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

