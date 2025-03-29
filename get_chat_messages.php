<?php
// エラー表示を無効化（JSONレスポンスを汚さないため）
ini_set('display_errors', 0);
error_reporting(0);

// レスポンスヘッダーの設定（最初に設定）
header('Content-Type: application/json');

// セッション開始
session_start();

// 必要なファイルの読み込み
require_once('funcs.php');

try {
    // ログインチェック
    if(!isset($_SESSION['chk_ssid']) || $_SESSION['chk_ssid'] != session_id()){
        throw new Exception("ログインエラー");
    }

    // セッションからユーザーIDを取得
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    if (!$user_id) {
        throw new Exception("ユーザー認証エラー");
    }

    // DB接続
    $pdo = db_conn();

    // チャットメッセージを取得
    $stmt = $pdo->prepare("SELECT id, message, is_user, created_at, 'chat' as type FROM chat_logs WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $chat_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 問題を取得
    $stmt = $pdo->prepare("SELECT id, question_text as message, 0 as is_user, created_at, 'question' as type FROM questions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // メッセージと問題を統合
    $all_items = array_merge($chat_messages, $questions);

    // 作成日時でソート（新しい順）
    usort($all_items, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // メッセージを整形
    $formatted_messages = array_map(function($item) use ($pdo) {
        $message = [
            'id' => $item['id'],
            'message' => $item['message'],
            'is_user' => $item['is_user'] == 1,
            'created_at' => $item['created_at'],
            'type' => $item['type']
        ];

        // 問題の場合は追加情報を含める
        if ($item['type'] === 'question') {
            $stmt = $pdo->prepare("SELECT option1, option2, option3, option4 FROM questions WHERE id = ?");
            $stmt->execute([$item['id']]);
            $options = $stmt->fetch(PDO::FETCH_ASSOC);
            $message['options'] = $options;
        }

        return $message;
    }, $all_items);

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'messages' => $formatted_messages
    ]);

} catch (Exception $e) {
    // エラーログに記録
    error_log("メッセージ取得エラー: " . $e->getMessage());
    
    // エラーレスポンス
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 