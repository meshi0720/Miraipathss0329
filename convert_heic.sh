#!/bin/bash

# アップロードディレクトリのパス
UPLOAD_DIR="/Applications/XAMPP/xamppfiles/htdocs/0329MiraiAngle/uploads"

# HEICファイルを検索して変換
find "$UPLOAD_DIR" -name "*.HEIC" -o -name "*.heic" | while read -r file; do
    # 出力ファイル名を設定（.HEICまたは.heicを.jpgに置換）
    output_file="${file%.*}.jpg"
    
    # 変換を実行
    sips -s format jpeg "$file" --out "$output_file"
    
    # 変換が成功したら元のファイルを削除
    if [ $? -eq 0 ]; then
        rm "$file"
        echo "変換成功: $file -> $output_file"
    else
        echo "変換失敗: $file"
    fi
done 