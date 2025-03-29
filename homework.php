<?php
session_start();
require_once('funcs.php');
loginCheck();

// DB接続
$pdo = db_conn();
$user_id = $_SESSION['user_id'];

// タスクの登録
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $homework_type = $_POST['homework_type'];
    $details = $_POST['details'];
    $due_date = $_POST['due_date'];

    // タイトルの修正
    $title = $subject . ' - ' . $homework_type;

    $stmt = $pdo->prepare("INSERT INTO tasks (title, details, due_date, category, status, user_id) VALUES (?, ?, ?, ?, '未完了', ?)");
    $stmt->execute([$title, $details, $due_date, $homework_type, $user_id]);

    header("Location: homework.php");
    exit;
}

// タスクの取得
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = '未完了' ORDER BY due_date ASC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// タスクの削除
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    header("Location: homework.php");
    exit;
}

// ユーザーのポイント取得
$sql = "SELECT points FROM usertable WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$currentPoints = $user ? $user['points'] : 0;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>宿題</title>
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

        h2 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        /* フォームスタイル */
        form {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            width: 100%;
            position: relative;
            overflow: visible;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        select, input[type="date"], textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 200px;
            background-color: var(--white);
            color: var(--text-color);
        }

        select {
            background-image: none;
        }

        select option {
            padding: 12px;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-color);
            background-color: var(--white);
            width: 100%;
            box-sizing: border-box;
        }

        select:focus, input[type="date"]:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* ボタンスタイル */
        button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
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

        /* ステータスボタン */
        .complete, .incomplete {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .complete {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .incomplete {
            background-color: #f44336;
            color: var(--white);
        }

        .complete:hover, .incomplete:hover {
            transform: translateY(-2px);
        }

        /* ポイント表示 */
        .points-display {
            background: var(--white);
            border-radius: 15px;
            padding: 1rem 2rem;
            margin: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--text-color);
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            form {
                padding: 1.5rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }

        /* 宿題の種類のセレクトボックス専用スタイル */
        #homework_type {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 200px;
            background-color: var(--white);
            color: var(--text-color);
            background-image: none;
            white-space: normal;
            word-wrap: break-word;
            overflow: visible;
            text-overflow: clip;
            height: auto;
            min-height: 45px;
        }

        #homework_type option {
            padding: 12px;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-color);
            background-color: var(--white);
            width: 100%;
            box-sizing: border-box;
            white-space: normal;
            word-wrap: break-word;
        }

        /* セレクトボックスのコンテナ */
        .form-group {
            margin-bottom: 1.5rem;
            width: 100%;
            position: relative;
            overflow: visible;
        }

        .form-group select {
            display: block;
            box-sizing: border-box;
            margin: 0;
            line-height: normal;
            max-width: 100%;
            overflow: visible;
        }

        /* 既存のselect要素のスタイルを上書き */
        select {
            background-image: none !important;
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
            max-width: 150px;
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

<script>
    function updateHomeworkTypes() {
        const homeworkOptions = {
            "国語": ["読解", "漢字練習", "作文", "音読", "漢字テスト", "読書感想文"],
            "算数": ["計算", "文章問題", "図形", "計算ドリル", "テスト対策", "復習"],
            "理科": ["実験レポート", "用語暗記", "観察記録", "テスト対策", "自由研究", "復習"],
            "社会": ["地理", "歴史", "公民", "テスト対策", "調べ学習", "復習"]
        };

        const subject = document.getElementById("subject").value;
        const homeworkSelect = document.getElementById("homework_type");
        homeworkSelect.innerHTML = "";

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "--宿題の種類を選択してください--";
        homeworkSelect.appendChild(defaultOption);

        if (subject in homeworkOptions) {
            homeworkOptions[subject].forEach(type => {
                const option = document.createElement("option");
                option.value = type;
                option.textContent = type;
                homeworkSelect.appendChild(option);
            });
        }
    }

    // ページ読み込み時に教科が選択されている場合、宿題の種類を更新
    document.addEventListener('DOMContentLoaded', function() {
        const subjectSelect = document.getElementById("subject");
        if (subjectSelect.value) {
            updateHomeworkTypes();
        }
    });

   function toggleTaskStatus(taskId, currentStatus) {
        fetch(`update_task_status.php?task_id=${taskId}&current_status=${currentStatus}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ステータスを更新
                const statusElement = document.getElementById(`status-${taskId}`);
                statusElement.innerHTML = data.new_status === '完了' 
                    ? '<span class="complete">完了</span>' 
                    : '<span class="incomplete">未完了</span>';

            // ポイントを更新
            document.getElementById("points").textContent = data.new_points + " pt";
            
            // 次回クリック用に新しいステータスを設定
            statusElement.setAttribute("onclick", `toggleTaskStatus(${taskId}, '${data.new_status}')`);
            } else {
                alert("エラー: " + data.message);
            }
        });
    }

    function updatePointsUI() {
        fetch('get_points.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("points").textContent = data.points + " pt";
            }
        });
    }

    // 完了ボタンのクリックイベント
    $('.complete-btn').click(function() {
        if (!confirm('この宿題を完了状態にしますか？')) {
            return;
        }

        const id = $(this).data('id');
        const row = $(this).closest('tr'); // クリックされたボタンが含まれる行を取得

        $.ajax({
            url: 'complete_task.php',
            type: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                if (response.success) {
                    // 成功した場合、該当の行を削除
                    row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('状態の更新に失敗しました: ' + response.message);
                }
            },
            error: function() {
                alert('エラーが発生しました');
            }
        });
    });
</script>

<form method="POST">
    <div class="form-group">
        <label for="subject">教科:</label>
        <select id="subject" name="subject" required onchange="updateHomeworkTypes()">
            <option value="">--選択してください--</option>
            <option value="国語">国語</option>
            <option value="算数">算数</option>
            <option value="理科">理科</option>
            <option value="社会">社会</option>
        </select>
    </div>
    <div class="form-group">
        <label for="homework_type">宿題の種類:</label>
        <select id="homework_type" name="homework_type" required>
            <option value="">--教科を選択してください--</option>
        </select>
    </div>
    <div class="form-group">
        <label for="details">詳細:</label>
        <textarea id="details" name="details"></textarea>
    </div>
    <div class="form-group">
        <label for="due_date">期限:</label>
        <input type="date" id="due_date" name="due_date" required>
    </div>
    <button type="submit">登録</button>
</form>

<div class="points-display">
    現在のポイント: <span id="points"><?= htmlspecialchars($currentPoints, ENT_QUOTES, 'UTF-8') ?> pt</span>
</div>

<h2>宿題一覧</h2>
<br>
<table>
    <thead>
        <tr>
            <th>タイトル</th>
            <th>期限</th>
            <th>カテゴリー</th>
            <th>ステータス</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($task['due_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($task['category'], ENT_QUOTES, 'UTF-8') ?></td>
                <td id="status-<?= $task['id'] ?>" onclick="toggleTaskStatus(<?= $task['id'] ?>, '<?= $task['status'] ?>')">
                    <span class="<?= $task['status'] === '完了' ? 'complete' : 'incomplete' ?>">
                        <?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td>
                    <a class="button" href="?delete=<?= $task['id'] ?>" onclick="return confirm('本当に削除しますか？')">削除</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
