<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please login to bookmark articles']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $news_id = filter_input(INPUT_POST, 'news_id', FILTER_VALIDATE_INT);
    $user_id = current_user()['id'];

    if (!$news_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid article ID']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND news_id = ?");
        $check->execute([$user_id, $news_id]);
        $existing = $check->fetch();

        if ($existing) {
            $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND news_id = ?");
            $del->execute([$user_id, $news_id]);
            echo json_encode(['status' => 'success', 'action' => 'removed']);
        } else {
            $ins = $pdo->prepare("INSERT INTO bookmarks (user_id, news_id) VALUES (?, ?)");
            $ins->execute([$user_id, $news_id]);
            echo json_encode(['status' => 'success', 'action' => 'added']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

