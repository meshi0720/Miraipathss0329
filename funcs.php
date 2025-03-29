<?php

//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

//DB接続
function db_conn()
{
    try {
        $db_name = "schoolchoice_db_test";    // データベース名
        $db_id   = "root";      // アカウント名
        $db_pw   = "";          // パスワード
        $db_host = "localhost"; // DBホスト
        $pdo = new PDO('mysql:dbname='.$db_name.';charset=utf8;host='.$db_host, $db_id, $db_pw);
        return $pdo;
    } catch (PDOException $e) {
        exit('DBConnectionError:'.$e->getMessage());
    }
}

//SQLエラー
function sql_error($stmt)
{
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    exit('SQLError:' . $error[2]);
}

//リダイレクト
function redirect($file_name)
{
    header('Location: ' . $file_name);
    exit();
}


// ログインチェク処理 loginCheck()
function loginCheck(){

    if(!isset($_SESSION['chk_ssid']) || $_SESSION['chk_ssid'] != session_id()){
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "ログインエラー"]);
        exit;
    }

session_regenerate_id(true);
$_SESSION['chk_ssid'] = session_id();

}

//API Keyの呼び出し
require_once('config.php'); // config.php を呼び出し

function getOpenAIKey() {
    return OPENAI_API_KEY; // config.php からAPIキーを取得
}

function call_gpt_3_5_turbo_api($messages, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ],
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        error_log("API呼び出しエラー: " . error_get_last()['message']);
        return false;
    }
    
    return $response;
}

// Vision APIの設定
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('OPENAI_VISION_API_URL', 'https://api.openai.com/v1/chat/completions');

// Vision APIを使用して画像からテキストを抽出する関数
function extract_text_from_image($image_data) {
    // APIキーを取得
    $api_key = getOpenAIKey();
    if (!$api_key) {
        throw new Exception("APIキーが取得できません");
    }

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ];

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'この画像からテキストを抽出してください。以下の点に注意してください：
1. 画像内のすべてのテキストを抽出してください
2. テキストの配置や構造も可能な限り保持してください
3. 日本語の場合は日本語で、英語の場合は英語で出力してください
4. テキストが見つからない場合は、その旨を明確に伝えてください'
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:image/jpeg;base64,' . $image_data
                        ]
                    ]
                ]
            ]
        ],
        'max_tokens' => 1000
    ];

    $ch = curl_init(OPENAI_VISION_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("CURLエラー: " . $curl_error);
        throw new Exception("APIリクエストに失敗しました: " . $curl_error);
    }

    if ($http_code !== 200) {
        error_log("Vision APIエラー: HTTP " . $http_code);
        error_log("レスポンス: " . $response);
        throw new Exception("画像の解析に失敗しました (HTTP " . $http_code . ")");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSONデコードエラー: " . json_last_error_msg());
        error_log("レスポンス: " . $response);
        throw new Exception("APIレスポンスの解析に失敗しました");
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        error_log("不正なレスポンス形式: " . print_r($result, true));
        throw new Exception("テキストの抽出に失敗しました");
    }

    return $result['choices'][0]['message']['content'];
}
