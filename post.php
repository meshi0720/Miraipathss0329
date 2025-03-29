<?php
session_start();
require_once('funcs.php');
loginCheck();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
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

        /* アンケートフォーム */
        .survey-form {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        /* ボタンスタイル */
        .btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            text-align: center;
            margin: 0.5rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* ラジオボタンとチェックボックス */
        .radio-group,
        .checkbox-group {
            margin: 1rem 0;
        }

        .radio-group label,
        .checkbox-group label {
            display: inline-block;
            margin-right: 1rem;
            font-weight: normal;
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .survey-form {
                padding: 1.5rem;
                margin: 1rem;
            }

            .form-group input[type="text"],
            .form-group input[type="number"],
            .form-group select,
            .form-group textarea {
                font-size: 16px; /* モバイルでの自動ズームを防ぐ */
            }
        }
    </style>
    <title>POSTアンケート</title>
</head>

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

<body>
    <div class="menu">
        <h3>学校選びのためのアンケート</h3>
        <ul>
            <li>以下の質問に答えてください</li>
            <li>いつ変えてもOK!何回出してもOK!</li>
        </ul>
    </div>

    <form action="insert.php" method="post">
        <br>
        <p>1.男子・女子校または共学どちらが良いですか？</p>
        <label><input type="radio" name="q1" value="共学"> 共学</label>
        <label><input type="radio" name="q1" value="男子校"> 男子校</label>
        <label><input type="radio" name="q1" value="女子校"> 女子校</label>
        <label><input type="radio" name="q1" value="こだわらない"> こだわらない</label><br>
        <br><br>

        <p>2. 制服はある方が良いですか？</p>
        <label><input type="radio" name="q2" value="ある"> ある</label>
        <label><input type="radio" name="q2" value="ない"> ない</label>
        <label><input type="radio" name="q2" value="こだわらない"> こだわらない</label><br>
        <br><br>

        <p>3. 通学時間はどこまで耐えられますか？</p>
        <label><input type="radio" name="q3" value="1時間以内"> 1時間以内</label>
        <label><input type="radio" name="q3" value="1時間半以内"> 1時間半以内</label>
        <label><input type="radio" name="q3" value="2時間以内"> 2時間以内</label><br>
        <br><br>

        <input type="submit" value="送信">
        <br>
    </form>
</body>

</html>
