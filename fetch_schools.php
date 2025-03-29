<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// エラーログを表示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 受信データを取得
$data = file_get_contents("php://input");

// デバッグ用ログ出力
file_put_contents("debug.log", "受信データ: " . $data . "\n", FILE_APPEND);

if (!$data) {
    echo json_encode(["message" => "受信データが空です"]);
    exit;
}

// JSONデコード
$decodedData = json_decode($data, true);
if (!$decodedData) {
    echo json_encode(["message" => "JSONのデコードに失敗しました", "error" => json_last_error_msg()]);
    exit;
}

// CSVファイル保存
$csv_file = __DIR__ . '/schools.csv';
$fp = fopen($csv_file, 'w');
if (!$fp) {
    echo json_encode(["message" => "CSVファイルを開けませんでした"]);
    exit;
}

// ヘッダー行
fputcsv($fp, ['学校名', '住所', '種別']);
foreach ($decodedData as $school) {
    fputcsv($fp, [$school['name'], $school['address'], $school['type']]);
}

fclose($fp);
echo json_encode(["message" => "CSVファイルを保存しました", "file" => "schools.csv"]);
?>
