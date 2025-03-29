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

// ログインチェック
if(!isset($_SESSION['chk_ssid']) || $_SESSION['chk_ssid'] != session_id()){
    echo json_encode([
        'success' => false,
        'message' => 'ログインエラー'
    ]);
    exit;
}

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ユーザー認証エラー'
    ]);
    exit;
}

try {
    // DB接続
    $pdo = db_conn();
    
    // APIキーの取得
    $api_key = getOpenAIKey();
    if (!$api_key) {
        throw new Exception("APIキーの設定エラー");
    }

    // チャットメッセージの場合
    if (isset($_POST['user_input'])) {
        $user_input = trim($_POST['user_input']);
        if (empty($user_input)) {
            echo json_encode([
                'success' => false,
                'message' => 'メッセージが入力されていません'
            ]);
            exit;
        }

        // ユーザーのメッセージを保存
        $stmt = $pdo->prepare("INSERT INTO chat_logs (user_id, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
        $stmt->execute([$user_id, $user_input]);

        // ChatGPT APIに送信
        $messages = [
            ["role" => "system", "content" => "You are a helpful assistant."],
            ["role" => "user", "content" => $user_input]
        ];
        
        $response = call_gpt_3_5_turbo_api($messages, $api_key);
        if (!$response) {
            throw new Exception("API呼び出しに失敗しました");
        }
        
        $response_decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("APIレスポンスの解析に失敗しました");
        }
        
        if (!isset($response_decoded["choices"][0]["message"]["content"])) {
            throw new Exception("APIからの応答が不正です");
        }
        
        $ai_response = trim($response_decoded["choices"][0]["message"]["content"]);
        
        // AIの返答をDBに保存
        $stmt = $pdo->prepare("INSERT INTO chat_logs (user_id, message, sender, created_at) VALUES (?, ?, 'bot', NOW())");
        $stmt->execute([$user_id, $ai_response]);

        echo json_encode([
            'success' => true,
            'message' => $ai_response
        ]);
        exit;
    }

    // 画像アップロードの場合
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_text = extract_text_from_image($target_file);
            
            if ($image_text) {
                // 画像から抽出したテキストを問題として保存
                $stmt = $pdo->prepare("INSERT INTO questions (user_id, question_text, option1, option2, option3, option4, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $user_id,
                    $image_text,
                    '選択肢1',
                    '選択肢2',
                    '選択肢3',
                    '選択肢4'
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => '画像から問題を生成しました'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '画像からテキストを抽出できませんでした'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => '画像のアップロードに失敗しました'
            ]);
        }
        exit;
    }

    // どちらの処理でもない場合
    echo json_encode([
        'success' => false,
        'message' => '無効なリクエストです'
    ]);

} catch (Exception $e) {
    // エラーログに記録
    error_log("APIエラー: " . $e->getMessage());
    
    // エラーレスポンス
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
