<?php

function get_root_prefix() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
    if (strpos($script, '/user/') !== false) {
        return '../';
    }
    if (preg_match('#/admin/[^/]+/#', $script)) {
        return '../../';
    }
    if (strpos($script, '/admin/') !== false) {
        return '../';
    }
    return '';
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function format_date($date_string, $include_time = false) {
    if (!$date_string) return 'N/A';
    $timestamp = strtotime($date_string);
    if ($include_time) {
        return date('M d, Y h:i A', $timestamp);
    }
    return date('M d, Y', $timestamp);
}

function time_ago($datetime) {
    if (!$datetime) return '';
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

function get_image_url($image_name) {
    $root = get_root_prefix();
    if (empty($image_name)) {
        return $root . 'assets/images/campus_hero.jpg';
    }

    if (strpos($image_name, 'http://') === 0 || strpos($image_name, 'https://') === 0) {
        return $image_name;
    }

    $clean_name = ltrim((string)$image_name, '/\\');
    while (strpos($clean_name, '../') === 0 || strpos($clean_name, '..\\') === 0 || strpos($clean_name, './') === 0 || strpos($clean_name, '.\\') === 0) {
        $clean_name = preg_replace('#^(\.\./|\.\.\\\\|\./|\.\\\\)#', '', $clean_name);
    }

    if (strpos($clean_name, 'assets/') === 0) {
        if (file_exists(__DIR__ . '/../' . $clean_name)) {
            return $root . $clean_name;
        }
    }

    if (file_exists(__DIR__ . '/../assets/uploads/' . $clean_name)) {
        return $root . 'assets/uploads/' . $clean_name;
    }

    if (file_exists(__DIR__ . '/../assets/images/' . $clean_name)) {
        return $root . 'assets/images/' . $clean_name;
    }

    $base_name = basename($clean_name);
    if (file_exists(__DIR__ . '/../assets/uploads/' . $base_name)) {
        return $root . 'assets/uploads/' . $base_name;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $base_name)) {
        return $root . 'assets/images/' . $base_name;
    }

    return $root . 'assets/images/campus_hero.jpg';
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login($message = 'Please sign in with your NSBM credentials to access campus news, articles, and announcements.') {
    if (!is_logged_in()) {
        $root = get_root_prefix();
        set_flash('warning', $message);
        $redirect = $_SERVER['REQUEST_URI'] ?? '';
        header("Location: {$root}login.php?redirect=" . urlencode($redirect));
        exit;
    }
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_student() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'         => $_SESSION['user_id'] ?? null,
        'name'       => $_SESSION['user_name'] ?? 'User',
        'email'      => $_SESSION['user_email'] ?? '',
        'role'       => $_SESSION['user_role'] ?? 'student',
        'faculty'    => $_SESSION['user_faculty'] ?? 'Faculty of Computing',
        'student_id' => $_SESSION['user_student_id'] ?? ''
    ];
}

function is_bookmarked($pdo, $user_id, $news_id) {
    if (!$user_id) return false;
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND news_id = ?");
    $stmt->execute([$user_id, $news_id]);
    return (bool)$stmt->fetch();
}

function is_subscribed($pdo, $user_id, $category_id) {
    if (!$user_id) return false;
    $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND category_id = ?");
    $stmt->execute([$user_id, $category_id]);
    return (bool)$stmt->fetch();
}

function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show shadow-sm\" role=\"alert\">
                <div class=\"d-flex align-items-center\">
                    <i class=\"bi " . ($type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger') . " me-2 fs-5\"></i>
                    <div>{$message}</div>
                </div>
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
              </div>";
    }
}

function get_category_badge($cat_name) {
    $colors = [
        'Academic'              => 'bg-primary',
        'Computing & Tech'      => 'bg-info text-dark',
        'Events & Festivals'    => 'bg-warning text-dark',
        'Sports & Fitness'      => 'bg-success',
        'Student Life & Clubs'  => 'bg-secondary',
        'General Announcements' => 'bg-dark'
    ];
    return $colors[$cat_name] ?? 'bg-success';
}
?>

