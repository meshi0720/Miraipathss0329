<?php
session_start();
require_once('funcs.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["image_id"])) {
    $image_id = $_POST["image_id"];

    // 画像パス取得
    $pdo = db_conn();
    $stmt = $pdo->prepare("SELECT image_path FROM uploaded_data WHERE id = :image_id");
    $stmt->bindValue(":image_id", $image_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $image_path = $row["image_path"];
        
        // Google Cloud Vision APIを使用してOCR処理
        $api_key = getGoogleCloudVisionKey(); // funcs.phpに実装が必要
        $image_data = file_get_contents($image_path);
        $base64_image = base64_encode($image_data);
        
        $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key;
        $data = [
            "requests" => [
                [
                    "image" => [
                        "content" => $base64_image
                    ],
                    "features" => [
                        [
                            "type" => "TEXT_DETECTION"
                        ]
                    ]
                ]
            ]
        ];
        
        $options = [
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json",
                "content" => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response, true);
        
        if (isset($result["responses"][0]["textAnnotations"][0]["description"])) {
            $extracted_text = $result["responses"][0]["textAnnotations"][0]["description"];
            
            // テキストをDBに保存
            $stmt = $pdo->prepare("UPDATE uploaded_data SET extracted_text = :extracted_text WHERE id = :image_id");
            $stmt->bindValue(":extracted_text", $extracted_text, PDO::PARAM_STR);
            $stmt->bindValue(":image_id", $image_id, PDO::PARAM_INT);
            $stmt->execute();
            
            echo json_encode(["success" => true, "extracted_text" => $extracted_text]);
        } else {
            echo json_encode(["success" => false, "error" => "テキストの抽出に失敗しました"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "画像が見つかりません"]);
    }
}
?>
