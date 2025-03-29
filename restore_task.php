<?php
session_start();
require_once('funcs.php');
loginCheck();

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('IDが指定されていません');
    }

    $pdo = db_conn();
    $task_id = $_POST['id'];
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

    if (!$user_id) {
        throw new Exception('ユーザー認証エラー');
    }

    // 宿題の状態を「未完了」に更新
    $stmt = $pdo->prepare("UPDATE tasks SET status = '未完了' WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('更新対象の宿題が見つかりません');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("エラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 