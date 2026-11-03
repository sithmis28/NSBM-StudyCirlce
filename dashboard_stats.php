<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!is_admin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Administrator authentication required.']);
    exit;
}

try {
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM news WHERE status = 'published') AS publishedNews,
            (SELECT COALESCE(SUM(views), 0) FROM news) AS totalViews,
            (SELECT COUNT(*) FROM article_submissions WHERE status = 'pending') AS pendingSubmissions,
            (SELECT COUNT(*) FROM users WHERE role = 'student') AS registeredStudents
    ")->fetch();

    echo json_encode([
        'publishedNews' => (int) $stats['publishedNews'],
        'totalViews' => (int) $stats['totalViews'],
        'pendingSubmissions' => (int) $stats['pendingSubmissions'],
        'registeredStudents' => (int) $stats['registeredStudents'],
    ]);
} catch (Throwable $exception) {
    error_log('Unable to load admin dashboard stats: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load dashboard stats.']);
}
