<?php
session_start();
require_once('funcs.php');

// エラー表示を無効化（JSONレスポンスのため）
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// エラーログの設定
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_error.log');

// 常にJSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

try {
    loginCheck();
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("画像のアップロードに失敗しました: " . 
            (isset($_FILES['image']['error']) ? $_FILES['image']['error'] : "ファイルが選択されていません"));
    }
    
    // デバッグ情報をログに出力
    error_log("アップロードされたファイル情報: " . print_r($_FILES['image'], true));
    
    // 画像サイズのチェック（最大5MB）
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        throw new Exception("画像サイズは5MB以下にしてください（現在のサイズ: " . 
            round($_FILES['image']['size'] / 1024 / 1024, 2) . "MB）");
    }
    
    // 一時ファイルのパス
    $temp_file = $_FILES['image']['tmp_name'];
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    
    // 画像の読み込み
    $image_info = getimagesize($temp_file);
    if ($image_info === false) {
        throw new Exception("画像ファイルの読み込みに失敗しました");
    }
    
    // 画像タイプのチェック
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $mime_type = $image_info['mime'];
    
    error_log("アップロードされたファイルのMIMEタイプ: " . $mime_type);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("JPG、PNG、GIF形式の画像のみアップロード可能です（現在の形式: " . $mime_type . "）");
    }
    
    // 画像を読み込む
    switch ($mime_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($temp_file);
            break;
        case 'image/png':
            $source = imagecreatefrompng($temp_file);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($temp_file);
            break;
        default:
            throw new Exception("サポートされていない画像形式です");
    }
    
    if ($source === false) {
        throw new Exception("画像の読み込みに失敗しました");
    }
    
    // 元の画像サイズを取得
    $width = imagesx($source);
    $height = imagesy($source);
    
    // 新しいサイズを計算（最大幅を1200pxに制限）
    $max_width = 1200;
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
    } else {
        $new_width = $width;
        $new_height = $height;
    }
    
    // 新しい画像を作成
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // 透明度を保持（PNGの場合）
    if ($mime_type === 'image/png') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // 画像をリサイズ
    imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // 一時ファイルとして保存（品質を60%に設定）
    $output_file = $temp_file . '.jpg';
    if (!imagejpeg($new_image, $output_file, 60)) {
        throw new Exception("画像の保存に失敗しました");
    }
    
    // メモリを解放
    imagedestroy($source);
    imagedestroy($new_image);
    
    $pdo = db_conn();
    
    // セッションからユーザーIDを取得
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    
    if (!$user_id) {
        throw new Exception("ユーザー認証エラー");
    }
    
    // 画像データをBase64エンコード
    $image_data = base64_encode(file_get_contents($output_file));
    
    // データベースに保存
    $stmt = $pdo->prepare("INSERT INTO uploaded_data (user_id, image_data) VALUES (?, ?)");
    if (!$stmt->execute([$user_id, $image_data])) {
        throw new Exception("データベースへの保存に失敗しました: " . implode(", ", $stmt->errorInfo()));
    }
    
    $image_id = $pdo->lastInsertId();
    
    // 一時ファイルを削除
    if (file_exists($output_file)) {
        unlink($output_file);
    }
    
    echo json_encode([
        'success' => true,
        'message' => '画像がアップロードされました',
        'image_id' => $image_id
    ]);
    
} catch (Exception $e) {
    error_log("エラー: " . $e->getMessage());
    error_log("スタックトレース: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
    exit;
}
?>
