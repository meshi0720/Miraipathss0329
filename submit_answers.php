<?php
session_start();
require_once('funcs.php');
loginCheck();

// エラー表示を無効化（JSONレスポンスを汚さないため）
ini_set('display_errors', 0);
error_reporting(0);

// レスポンスヘッダーの設定
header('Content-Type: application/json');

try {
    if (!isset($_POST['question_id']) || !isset($_POST['answer'])) {
        throw new Exception('必要なパラメータが不足しています');
    }

    $pdo = db_conn();
    $question_id = $_POST['question_id'];
    $answer = $_POST['answer'];
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

    if (!$user_id) {
        throw new Exception('ユーザー認証エラー');
    }

    // 問題の取得
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ? AND user_id = ?");
    $stmt->execute([$question_id, $user_id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        throw new Exception('問題が見つかりません');
    }

    // 正解かどうかを判定
    $is_correct = ($answer === $question['correct_answer']) ? 1 : 0;
    $points = $is_correct ? 1 : 0;
    $result = $is_correct ? '正解' : '不正解';

    // 解答を保存
    $stmt = $pdo->prepare("UPDATE questions SET user_answer = ?, is_correct = ?, points = ?, result = ? WHERE id = ?");
    $stmt->execute([$answer, $is_correct, $points, $result, $question_id]);

    // ユーザーのポイントを更新
    $stmt = $pdo->prepare("UPDATE usertable SET points = points + ? WHERE id = ?");
    $stmt->execute([$points, $user_id]);

    echo json_encode([
        'success' => true,
        'points' => $points,
        'result' => $result
    ]);

} catch (Exception $e) {
    error_log("エラー: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 