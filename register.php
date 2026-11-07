<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header("Location: user/dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $account_type = trim($_POST['account_type'] ?? 'student');
    $admin_code = trim($_POST['admin_code'] ?? '');

    $role = 'student';
    if ($account_type === 'admin') {
        if ($admin_code !== 'admin123' && $admin_code !== 'nsbm2026') {
            $error = "Invalid Administrator Security Passcode. (Passcode: admin123)";
        } else {
            $role = 'admin';
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($email) || empty($password) || empty($faculty)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "An account with this email address already exists.";
            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare("
                    INSERT INTO users (name, email, student_id, faculty, password, role)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                if ($insert_stmt->execute([$name, $email, $student_id, $faculty, $hashed_password, $role])) {
                    $new_user_id = $pdo->lastInsertId();

                    if ($role === 'student') {
                        $general_cat = $pdo->query("SELECT id FROM categories WHERE slug = 'general-announcements'")->fetchColumn();
                        if ($general_cat) {
                            $pdo->prepare("INSERT INTO subscriptions (user_id, category_id) VALUES (?, ?)")->execute([$new_user_id, $general_cat]);
                        }
                    }

                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_faculty'] = $faculty;
                    $_SESSION['user_student_id'] = $student_id;

                    set_flash('success', "Account created successfully! Welcome to NSBM NewsDesk, {$name}.");
                    if ($role === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: user/dashboard.php");
                    }
                    exit;
                } else {
                    $error = "Failed to register account. Please try again.";
                }
            }
        }
    }
}

$page_title = "Student Registration";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-sm-5">
                <div class="text-center mb-4">
                    <img src="assets/images/logo_nsbm.png" alt="NSBM" style="height: 52px;" class="mb-3">
                    <h3 class="fw-bold text-navy">Student Registration</h3>
                    <p class="text-muted small">Join NSBM NewsDesk to bookmark articles, subscribe to feeds, and submit club stories</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <small><?php echo htmlspecialchars($error); ?></small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold text-navy small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Kavindu Perera" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="student_id" class="form-label fw-semibold text-navy small">Student ID / Batch Reg No.</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="e.g. 26.1-IFSL-045" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="faculty" class="form-label fw-semibold text-navy small">Faculty <span class="text-danger">*</span></label>
                            <select class="form-select" id="faculty" name="faculty" required>
                                <option value="">Select Faculty...</option>
                                <option value="Faculty of Computing" <?php echo (($_POST['faculty'] ?? '') === 'Faculty of Computing') ? 'selected' : ''; ?>>Faculty of Computing</option>
                                <option value="Faculty of Business" <?php echo (($_POST['faculty'] ?? '') === 'Faculty of Business') ? 'selected' : ''; ?>>Faculty of Business</option>
                                <option value="Faculty of Engineering" <?php echo (($_POST['faculty'] ?? '') === 'Faculty of Engineering') ? 'selected' : ''; ?>>Faculty of Engineering</option>
                                <option value="Faculty of Science" <?php echo (($_POST['faculty'] ?? '') === 'Faculty of Science') ? 'selected' : ''; ?>>Faculty of Science</option>
                            </select>
                            <div class="invalid-feedback">Please select your faculty.</div>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold text-navy small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="student@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold text-navy small">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" minlength="6" required>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label fw-semibold text-navy small">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                            <div class="invalid-feedback">Please confirm your password.</div>
                        </div>

                        <div class="col-12">
                            <label for="account_type" class="form-label fw-semibold text-navy small">Register As</label>
                            <select class="form-select" id="account_type" name="account_type" onchange="toggleAdminCode(this.value)">
                                <option value="student" <?php echo (($_POST['account_type'] ?? 'student') === 'student') ? 'selected' : ''; ?>>Undergraduate Student (Default)</option>
                                <option value="admin" <?php echo (($_POST['account_type'] ?? '') === 'admin') ? 'selected' : ''; ?>>System Administrator / Staff</option>
                            </select>
                        </div>

                        <div class="col-12" id="adminCodeGroup" style="<?php echo (($_POST['account_type'] ?? '') === 'admin') ? '' : 'display:none;'; ?>">
                            <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle">
                                <label for="admin_code" class="form-label fw-bold text-danger small mb-1">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Administrator Security Passcode:
                                </label>
                                <input type="password" class="form-control border-danger" id="admin_code" name="admin_code" placeholder="Enter admin security key (Passcode: admin123)">
                                <small class="text-muted d-block mt-1">Default security key for demo/evaluation: <code>admin123</code></small>
                            </div>
                        </div>
                    </div>

                    <script>
                    function toggleAdminCode(val) {
                        var grp = document.getElementById('adminCodeGroup');
                        if (val === 'admin') {
                            grp.style.display = 'block';
                        } else {
                            grp.style.display = 'none';
                        }
                    }
                    </script>

                    <button type="submit" class="btn btn-nsbm w-100 py-2 fw-bold mt-4 mb-3 shadow-sm">
                        <i class="bi bi-person-check-fill me-1"></i> Register Account
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-0">Already have an NSBM NewsDesk account? <a href="login.php" class="fw-bold text-success text-decoration-none">Sign In &rarr;</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

