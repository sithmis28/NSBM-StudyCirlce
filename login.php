<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}

$redirect = $_GET['redirect'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email address and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            $password_valid = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $password_valid = true;
                } elseif (hash('sha256', $password) === $user['password']) {
                    $password_valid = true;
                }
            }

            if ($user && $password_valid) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_faculty'] = $user['faculty'];
                $_SESSION['user_student_id'] = $user['student_id'];

                set_flash('success', "Welcome back, {$user['name']}!");

                if (!empty($redirect) && filter_var($redirect, FILTER_SANITIZE_URL)) {
                    header("Location: " . $redirect);
                } else {
                    if ($user['role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                }
                exit;
            } else {
                $error = "Invalid email or password. Please verify your credentials.";
            }
        } catch (Exception $e) {
            $error = "A system error occurred. Please try again later.";
        }
    }
}

$page_title = "Sign In";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-sm-5">
                <div class="text-center mb-4">
                    <img src="assets/images/logo_nsbm.png" alt="NSBM" style="height: 52px;" class="mb-3">
                    <h3 class="fw-bold text-navy">Welcome to NewsDesk</h3>
                    <p class="text-muted small">Sign in to manage news, bookmarks, comments and subscriptions</p>
                </div>

                <?php display_flash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <small><?php echo htmlspecialchars($error); ?></small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-navy small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope text-success"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-navy small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock text-success"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-nsbm w-100 py-2 fw-bold mb-3 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-1">New undergraduate or club coordinator?</p>
                    <a href="register.php" class="fw-bold text-success text-decoration-none">
                        Create an NSBM Student Account &rarr;
                    </a>
                </div>

                <div class="mt-4 p-3 bg-light rounded-3 border small">
                    <div class="fw-bold text-navy mb-2"><i class="bi bi-key-fill text-warning me-1"></i> Demo Login Credentials:</div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <span class="badge bg-danger me-1">Admin</span>
                            <span class="text-dark fw-semibold">admin@gmail.com</span>
                            <small class="text-muted d-block">Password: <code>admin123</code></small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" onclick="fillLogin('admin@gmail.com', 'admin123')">
                            Fill Admin
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-success me-1">Student</span>
                            <span class="text-dark fw-semibold">kavindu@gmail.com</span>
                            <small class="text-muted d-block">Password: <code>student123</code></small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success px-2 py-1" onclick="fillLogin('kavindu@gmail.com', 'student123')">
                            Fill Student
                        </button>
                    </div>
                </div>

                <script>
                function fillLogin(email, password) {
                    document.getElementById('email').value = email;
                    document.getElementById('password').value = password;
                }
                </script>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

