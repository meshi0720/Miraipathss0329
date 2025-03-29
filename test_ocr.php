<?php
session_start();
require_once('funcs.php');

// エラー表示を有効化（テスト用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// エラーログの設定
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_error.log');

try {
    // ログインチェック
    if(!isset($_SESSION['chk_ssid']) || $_SESSION['chk_ssid'] != session_id()){
        throw new Exception("ログインエラー");
    }
    
    // 最新の画像を取得
    $pdo = db_conn();
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    
    if (!$user_id) {
        throw new Exception("ユーザー認証エラー");
    }
    
    // 最新の画像データを取得
    $stmt = $pdo->prepare("SELECT id, image_data FROM uploaded_data WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception("画像が見つかりません");
    }
    
    // 画像データのサイズを確認
    $image_size = strlen($result['image_data']);
    echo "<p>画像データサイズ: " . $image_size . " bytes</p>";
    
    // OCR処理を実行
    $extracted_text = extract_text_from_image($result['image_data']);
    
    // OCR結果をデータベースに保存
    $update_stmt = $pdo->prepare("UPDATE uploaded_data SET extracted_text = ? WHERE id = ?");
    $update_result = $update_stmt->execute([$extracted_text, $result['id']]);
    
    if (!$update_result) {
        throw new Exception("OCR結果の保存に失敗しました");
    }
    
    // 結果を表示
    echo "<h2>OCR処理結果</h2>";
    echo "<pre>" . htmlspecialchars($extracted_text) . "</pre>";
    echo "<p>OCR結果をデータベースに保存しました。</p>";
    
} catch (Exception $e) {
    error_log("OCRテストエラー: " . $e->getMessage());
    error_log("スタックトレース: " . $e->getTraceAsString());
    
    echo "<h2>エラー</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?> 