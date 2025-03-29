<?php
session_start();
require_once('funcs.php');

try {
    loginCheck();
    
    if (!isset($_POST['question_id']) || !isset($_POST['result'])) {
        throw new Exception("必要なパラメータが不足しています");
    }
    
    $pdo = db_conn();
    
    $stmt = $pdo->prepare("UPDATE questions SET result = ? WHERE id = ?");
    $stmt->execute([$_POST['result'], $_POST['question_id']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("エラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 