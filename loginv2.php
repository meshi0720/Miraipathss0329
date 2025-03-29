<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ミライ☆パス</title>
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

        /* ログインフォーム */
        .login-form {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 500px;
            margin: 2rem auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .login-btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .login-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .login-form {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="header-list">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="homework.php">Homework</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="school.php">School</a></li>
            <li><a href="post.php">Survey</a></li>
            <li><a href="history.php">履歴一覧</a></li>
        </ul>
    </div>
</header>

    <div class="container">
        <img src="./image/futurepath(&ss)logo1.png" alt="explore image" />
    </div>

    <h1 class="cotainer1">
        <b>自分のミライを創りに行こう！</b>
        <p>未来を作る冒険譚<br>「ミライ☆パス」</p>
    </h1>

    <br><br>
    <form name="form1" action="login_act.php" method="post">
        ID:<input type="text" name="lid" />
        PW:<input type="password" name="lpw" autocomplete="current-password">
        <input type="submit" value="LOGIN" />
    </form>
    <br><br>
</body>


