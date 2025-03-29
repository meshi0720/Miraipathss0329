<?php
session_start();
?><!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アンケート入力フォーム</title>
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
            --error-color: #f44336;    /* エラー色 */
        }

        /* 基本スタイル */
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Hiragino Kaku Gothic Pro', 'メイリオ', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* ヘッダー */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .logo {
            text-align: center;
            margin-bottom: 1rem;
        }

        .logo img {
            max-width: 200px;
            height: auto;
        }

        .header-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .header-list ul li a {
            color: var(--text-color);
            text-decoration: none;
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
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--primary-color);
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        /* フォームスタイル */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        /* ボタンスタイル */
        button[type="submit"] {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* エラーメッセージ */
        .error {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .header-list ul {
                flex-direction: column;
                align-items: center;
            }

            .header-list ul li {
                margin: 0.5rem 0;
            }

            button[type="submit"] {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>

<body>
    <header>
        <p class="logo">
            <a href="#">
                <img src="./image/futurepath(&ss)logo1.png" alt="Cheese Academy Tokyo">
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
        <h1>アンケート入力フォーム</h1>

        <form action="submit_survey.php" method="post">
            <div class="form-group">
                <label for="name">お名前</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="age">年齢</label>
                <input type="number" id="age" name="age" required>
            </div>

            <div class="form-group">
                <label for="gender">性別</label>
                <select id="gender" name="gender" required>
                    <option value="">選択してください</option>
                    <option value="男性">男性</option>
                    <option value="女性">女性</option>
                    <option value="その他">その他</option>
                </select>
            </div>

            <div class="form-group">
                <label for="feedback">フィードバック</label>
                <textarea id="feedback" name="feedback" rows="4" required></textarea>
            </div>

            <div class="form-group" style="text-align: center;">
                <button type="submit">送信</button>
            </div>
        </form>
    </div>
</body>
</html> 