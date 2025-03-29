<?php
session_start();
require_once('funcs.php');
loginCheck();

// デバッグ用のセッション情報出力
error_log("セッション情報: " . print_r($_SESSION, true));

// DB接続
$pdo = db_conn();

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if (!$user_id) {
    error_log("ユーザーIDが見つかりません: " . print_r($_SESSION, true));
    die("ユーザー認証エラー");
}

// デバッグ用のユーザーID出力
error_log("使用するユーザーID: " . $user_id);

// 各テーブルからデータを取得
try {
    // 宿題の完了一覧
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = '完了' ORDER BY due_date DESC");
    $stmt->execute([$user_id]);
    $completed_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("宿題クエリ: " . $stmt->queryString);
    error_log("宿題パラメータ: " . print_r([$user_id], true));

    // チャット履歴
    $stmt = $pdo->prepare("SELECT * FROM chat_logs WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $chat_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("チャットクエリ: " . $stmt->queryString);
    error_log("チャットパラメータ: " . print_r([$user_id], true));

    // 問題と解答一覧
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? AND user_answer IS NOT NULL ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("問題クエリ: " . $stmt->queryString);
    error_log("問題パラメータ: " . print_r([$user_id], true));

    // アンケート結果
    $stmt = $pdo->prepare("SELECT * FROM answer1 ORDER BY date DESC");
    $stmt->execute();
    $survey_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("アンケートクエリ: " . $stmt->queryString);

    // デバッグ用のログ出力
    error_log("取得したデータ数:");
    error_log("宿題: " . count($completed_tasks));
    error_log("チャット: " . count($chat_logs));
    error_log("問題: " . count($questions));
    error_log("アンケート: " . count($survey_results));

} catch (PDOException $e) {
    error_log("データベースエラー: " . $e->getMessage());
    $completed_tasks = [];
    $chat_logs = [];
    $questions = [];
    $survey_results = [];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>履歴一覧</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .tab-container {
            margin: 20px 0;
        }

        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            border: none;
            background: #f0f0f0;
            cursor: pointer;
            margin-right: 5px;
        }

        .tab-button.active {
            background: #007bff;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .message {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .delete-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .restore-btn {
            padding: 5px 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .restore-btn:hover {
            background-color: #218838;
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

    <main>
        <h1>履歴一覧</h1>

        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="tasks">宿題完了一覧</button>
                <button class="tab-button" data-tab="chat">チャット履歴</button>
                <button class="tab-button" data-tab="questions">問題と解答</button>
                <button class="tab-button" data-tab="survey">アンケート結果</button>
            </div>

            <!-- 宿題完了一覧 -->
            <div class="tab-content active" id="tasks">
                <h2>宿題完了一覧</h2>
                <table>
                    <thead>
                        <tr>
                            <th>タイトル</th>
                            <th>詳細</th>
                            <th>期限</th>
                            <th>カテゴリー</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td class="message"><?php echo htmlspecialchars($task['details']); ?></td>
                                <td><?php echo date('Y/m/d', strtotime($task['due_date'])); ?></td>
                                <td><?php echo htmlspecialchars($task['category']); ?></td>
                                <td>
                                    <button class="delete-btn" data-id="<?php echo $task['id']; ?>" data-type="task">削除</button>
                                    <button class="restore-btn" data-id="<?php echo $task['id']; ?>" data-type="task">元に戻す</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- チャット履歴 -->
            <div class="tab-content" id="chat">
                <h2>チャット履歴</h2>
                <table>
                    <thead>
                        <tr>
                            <th>送信者</th>
                            <th>メッセージ</th>
                            <th>送信日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chat_logs as $log): ?>
                            <tr>
                                <td><?php echo $log['sender'] === 'user' ? 'ユーザー' : 'AI'; ?></td>
                                <td class="message"><?php echo htmlspecialchars($log['message']); ?></td>
                                <td><?php echo date('Y/m/d H:i', strtotime($log['created_at'])); ?></td>
                                <td>
                                    <button class="delete-btn" data-id="<?php echo $log['id']; ?>" data-type="chat">削除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 問題と解答 -->
            <div class="tab-content" id="questions">
                <h2>問題と解答</h2>
                <table>
                    <thead>
                        <tr>
                            <th>問題</th>
                            <th>選択肢</th>
                            <th>解答</th>
                            <th>正解</th>
                            <th>正解/不正解</th>
                            <th>獲得ポイント</th>
                            <th>結果</th>
                            <th>解答日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td class="message"><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td>
                                    1: <?php echo htmlspecialchars($question['option1']); ?><br>
                                    2: <?php echo htmlspecialchars($question['option2']); ?><br>
                                    3: <?php echo htmlspecialchars($question['option3']); ?><br>
                                    4: <?php echo htmlspecialchars($question['option4']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($question['user_answer']); ?></td>
                                <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                                <td><?php echo $question['is_correct'] ? '正解' : '不正解'; ?></td>
                                <td><?php echo $question['points']; ?></td>
                                <td><?php echo htmlspecialchars($question['result']); ?></td>
                                <td><?php echo date('Y/m/d H:i', strtotime($question['created_at'])); ?></td>
                                <td>
                                    <button class="delete-btn" data-id="<?php echo $question['id']; ?>" data-type="question">削除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- アンケート結果 -->
            <div class="tab-content" id="survey">
                <h2>アンケート結果</h2>
                <table>
                    <thead>
                        <tr>
                            <th>質問1</th>
                            <th>質問2</th>
                            <th>質問3</th>
                            <th>回答日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($survey_results as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['q1']); ?></td>
                                <td><?php echo htmlspecialchars($result['q2']); ?></td>
                                <td><?php echo htmlspecialchars($result['q3']); ?></td>
                                <td><?php echo date('Y/m/d H:i', strtotime($result['date'])); ?></td>
                                <td>
                                    <button class="delete-btn" data-id="<?php echo $result['id']; ?>" data-type="survey">削除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // タブ切り替え
            $('.tab-button').click(function() {
                $('.tab-button').removeClass('active');
                $(this).addClass('active');
                
                const tabId = $(this).data('tab');
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });

            // 削除ボタンのクリックイベント
            $('.delete-btn').click(function() {
                if (!confirm('この履歴を削除してもよろしいですか？')) {
                    return;
                }

                const id = $(this).data('id');
                const type = $(this).data('type');

                $.ajax({
                    url: 'delete_history.php',
                    type: 'POST',
                    data: {
                        id: id,
                        type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('削除に失敗しました: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('エラーが発生しました');
                    }
                });
            });

            // 元に戻すボタンのクリックイベント
            $('.restore-btn').click(function() {
                if (!confirm('この宿題を未完了状態に戻してもよろしいですか？')) {
                    return;
                }

                const id = $(this).data('id');
                const type = $(this).data('type');
                const row = $(this).closest('tr'); // クリックされたボタンが含まれる行を取得

                $.ajax({
                    url: 'restore_task.php',
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
        });
    </script>
</body>

</html> 