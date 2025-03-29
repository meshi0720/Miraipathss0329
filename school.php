<?php
session_start();
require_once('funcs.php');
loginCheck();
$pdo = db_conn(); // DB接続

// 学校データ取得
$stmt = $pdo->prepare("SELECT * FROM schools");
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>志望校選びアンケートV1</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* 全体のカラーパレット */
        :root {
            --primary-color: #4CAF50;  /* メインの緑色 */
            --primary-light: #81C784;  /* 明るい緑 */
            --primary-dark: #388E3C;   /* 暗い緑 */
            --secondary-color: #FFC107; /* アクセントの黄色 */
            --text-color: #333333;     /* テキストの色 */
            --background-color: #F5F5F5; /* 背景色 */
            --white: #FFFFFF;
        }

        /* 基本スタイル */
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Hiragino Kaku Gothic Pro', 'メイリオ', sans-serif;
            line-height: 1.6;
        }

        /* ヘッダー */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .header-list ul li a {
            color: var(--text-color);
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .header-list ul li a:hover {
            background-color: var(--primary-light);
            color: var(--white);
        }

        /* メインコンテンツ */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        /* 学校カード */
        .school-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .school-card:hover {
            transform: translateY(-5px);
        }

        .school-card h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .school-card p {
            color: var(--text-color);
            font-size: 1.1rem;
            line-height: 1.8;
        }

        /* テーブルスタイル */
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 2rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--background-color);
        }

        th {
            background-color: var(--primary-light);
            color: var(--white);
            font-weight: bold;
        }

        tr:hover {
            background-color: var(--background-color);
        }

        /* ボタンスタイル */
        .button, button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }

        .button:hover, button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* 削除ボタン */
        .delete-btn {
            background-color: #f44336;
            color: var(--white);
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .school-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th, td {
                padding: 0.8rem;
            }

            .button, button {
                padding: 8px 16px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
<header>
        <p class="logo">
            <a href="#">
                <img src="./image/futurepath(&ss)logo1.png" alt="Cheese Academy Tokyo"/>
            </a>
        </p>    

        <nav class="header-list">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="homework.php">Homework</a></li>
                <li><a href="chat.php">Chat</a></li>
                <li><a href="school.php">School</a></li>
                <li><a href="post.php">Survey</a></li>
                <li><a href="history.php">履歴一覧</a></li>
            </ul>
        </nav>
</header>

    <div class="container">
        <h1>君が行きたいと思える学校を探してみよう！</h1>
        <br><br>
    </div>
    <br><br>

    <div>
        <h1>通学できる範囲にある学校一覧</h1>
        <button id="toggleView">お気に入りのみ表示</button>
        <br><br>
        <table id="schoolTable" border="1">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>学校名</th>
                    <th>住所</th>
                    <th>種別</th>
                    <th>お気に入り</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schools as $school): ?>
                    <tr data-id="<?= $school['id'] ?>" data-fav="<?= $school['favorite'] ?>">
                        <td><input type="checkbox"></td>
                        <td><?= htmlspecialchars($school['name']) ?></td>
                        <td><?= htmlspecialchars($school['location']) ?></td>
                        <td><?= htmlspecialchars($school['school_type']) ?></td>
                        <td>
                            <button class="favorite-btn" data-id="<?= $school['id'] ?>" data-fav="<?= $school['favorite'] ?>">
                                <?= $school['favorite'] ? "★" : "☆" ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
    </div>

    <script>
        document.querySelectorAll(".favorite-btn").forEach(button => {
            button.addEventListener("click", async () => {
                const schoolId = button.dataset.id;
                const currentFav = button.dataset.fav === "1" ? 0 : 1;

                const response = await fetch("update_favorite.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: schoolId, favorite: currentFav })
                });

                const result = await response.json();
                if (result.success) {
                    button.innerText = currentFav ? "★" : "☆";
                    button.dataset.fav = currentFav;
                    button.closest("tr").dataset.fav = currentFav;
                } else {
                    alert("更新に失敗しました");
                }
            });
        });

        document.getElementById("toggleView").addEventListener("click", () => {
            const tableRows = document.querySelectorAll("#schoolTable tbody tr");
            const showFavorites = document.getElementById("toggleView").innerText === "お気に入りのみ表示";
            
            tableRows.forEach(row => {
                if (showFavorites) {
                    row.style.display = row.dataset.fav === "1" ? "" : "none";
                } else {
                    row.style.display = "";
                }
            });
            document.getElementById("toggleView").innerText = showFavorites ? "全体表示" : "お気に入りのみ表示";
        });
    </script>
</body>
</html>
