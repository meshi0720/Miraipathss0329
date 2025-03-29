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
    
    // データベース接続
    $pdo = db_conn();
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    
    if (!$user_id) {
        throw new Exception("ユーザー認証エラー");
    }
    
    // 最新のOCR結果を取得
    $stmt = $pdo->prepare("SELECT id, extracted_text FROM uploaded_data WHERE user_id = ? AND extracted_text IS NOT NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception("OCR結果が見つかりません");
    }
    
    $image_id = $result['id']; // 画像IDを保存
    
    // ChatGPT APIを使用して問題を生成
    $api_key = getOpenAIKey();
    if (!$api_key) {
        throw new Exception("APIキーが取得できません");
    }
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ];
    
    $prompt = "以下のテキストから、10個の問題を生成してください。各問題には4つの選択肢と正解を含めてください。
    問題は以下の形式でJSON形式で出力してください：
    {
        \"questions\": [
            {
                \"question\": \"問題文\",
                \"options\": [\"選択肢1\", \"選択肢2\", \"選択肢3\", \"選択肢4\"],
                \"correct_answer\": \"正解の選択肢\"
            }
        ]
    }
    
    テキスト：
    " . $result['extracted_text'];
    
    $data = [
        'model' => 'gpt-4',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
    
    $ch = curl_init(OPENAI_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        throw new Exception("APIリクエストに失敗しました: " . $curl_error);
    }
    
    if ($http_code !== 200) {
        throw new Exception("問題生成に失敗しました (HTTP " . $http_code . ")");
    }
    
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("APIレスポンスの解析に失敗しました");
    }
    
    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception("問題の生成に失敗しました");
    }
    
    $questions = json_decode($result['choices'][0]['message']['content'], true);
    if (!$questions || !isset($questions['questions'])) {
        throw new Exception("生成された問題の形式が不正です");
    }
    
    // 問題をデータベースに保存
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, image_id, question_text, option1, option2, option3, option4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($questions['questions'] as $q) {
        $stmt->execute([
            $user_id,
            $image_id,
            $q['question'],
            $q['options'][0],
            $q['options'][1],
            $q['options'][2],
            $q['options'][3],
            $q['correct_answer']
        ]);
    }
    
    // 結果を表示
    echo "<h2>問題生成結果</h2>";
    echo "<p>10個の問題を生成し、データベースに保存しました。</p>";
    echo "<pre>" . htmlspecialchars(json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
} catch (Exception $e) {
    error_log("問題生成エラー: " . $e->getMessage());
    error_log("スタックトレース: " . $e->getTraceAsString());
    
    echo "<h2>エラー</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
