<?php
session_start();
require_once('funcs.php');
loginCheck();

// DB接続
$pdo = db_conn();

// セッションからユーザーIDを取得（両方の可能性に対応）
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if (!$user_id) {
    error_log("ユーザーIDが見つかりません: " . print_r($_SESSION, true));
    die("ユーザー認証エラー");
}

// 最新の問題を取得
try {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? AND user_answer IS NULL ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("データベースエラー: " . $e->getMessage());
    $questions = []; // エラー時は空の配列を設定
}
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

        /* チャットの吹き出しスタイル */
        #output {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px 0;
            height: 400px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .user-message {
            background: var(--primary-light);
            color: var(--white);
            border-radius: 20px 20px 0 20px;
            padding: 12px 20px;
            max-width: 80%;
            margin-left: auto;
        }

        .bot-message {
            background: var(--white);
            border: 2px solid var(--primary-color);
            border-radius: 20px 20px 20px 0;
            padding: 12px 20px;
            max-width: 80%;
        }

        /* フォーム要素 */
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            margin: 10px 0;
            font-size: 1rem;
        }

        button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* 問題表示 */
        .question-container {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
            padding: 20px;
        }

        .question-box {
            background: var(--background-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .question-box h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .options {
            background: var(--white);
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
        }

        .options p {
            margin: 10px 0;
            padding: 8px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .options p:hover {
            background-color: var(--primary-light);
            color: var(--white);
        }

        /* 解答フォーム */
        .answer-form {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .answer-select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 1rem;
            margin: 10px 0;
        }

        /* 画像アップロード */
        #upload-form {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .message {
                flex-direction: column;
            }

            .user-message, .bot-message {
                max-width: 100%;
            }

            .question-container {
                margin: 10px 0;
                padding: 15px;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    <!-- コンテンツ表示画面 -->
    <div>
        <h1>旅の相棒と出会おう！</h1>
        <br>
        <p>これからの長旅には楽しいことも辛いことも起こるはず。そんな時、仲間がいると楽しいことはもっと楽しく、辛いことはみんなで分け合って、、、</p>
        <br>
        <div><button id="meet">相棒に会う</button></div>
        <br>
        <div class="partner">
            <img src="./image/partner5.jpg" alt="partner image" />
        </div>
        <br><br>    
        
        <div>
            <div>相棒と会話しよう</div>
            <br>
            
            <form action="chat_api.php" method="post">
                <p>何をしたい？</p>
                <label><input type="radio" name="q1" value="おはなし"> おはなし</label>
                <label><input type="radio" name="q1" value="テスト作成"> テスト出してもらう</label>
                <label><input type="radio" name="q1" value="相棒に会う"> 相棒に会う</label>
                <br><br>

                <div>
                    <input type="text" id="uname" placeholder="名前を入力"><br><br>
                </div>
                <textarea id="text" placeholder="文章を入力してください" cols="50" rows="10"></textarea>
                <br><br>
                <button type="button" id="send">送信</button>
            </form>

            <form id="upload-form" method="POST" enctype="multipart/form-data">
                <input type="file" id="image-upload" accept="image/jpeg,image/png,image/gif">
                <button type="button" id="upload-btn">画像をアップロード</button>
            </form>

            <!-- カメラを起動するボタン -->
            <button type="button" id="start-camera">写真を撮る</button>
            <video id="camera" width="300" height="200" autoplay></video>
            <canvas id="canvas" width="300" height="200"></canvas>
            <button type="button" id="capture-btn" style="display:none;">撮影する</button>        

            <div id="upload-status"></div>

            <h2>生成された問題</h2>
            <?php if (empty($questions)): ?>
                <p>まだ問題は生成されていません。</p>
            <?php else: ?>
                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-container">
                        <!-- 問題表示 -->
                        <div class="question-box">
                            <h3>問題<?php echo $index + 1; ?></h3>
                            <p><?php echo htmlspecialchars($q['question_text']); ?></p>
                            <div class="options">
                                <p>1. <?php echo htmlspecialchars($q['option1']); ?></p>
                                <p>2. <?php echo htmlspecialchars($q['option2']); ?></p>
                                <p>3. <?php echo htmlspecialchars($q['option3']); ?></p>
                                <p>4. <?php echo htmlspecialchars($q['option4']); ?></p>
                            </div>
                        </div>

                        <!-- 解答入力フォーム -->
                        <div class="answer-form">
                            <div class="question-answer">
                                <p>問題<?php echo $index + 1; ?>の解答</p>
                                <select id="answer-<?php echo $q['id']; ?>" class="answer-select">
                                    <option value="">選択してください</option>
                                    <option value="<?php echo htmlspecialchars($q['option1']); ?>">1. <?php echo htmlspecialchars($q['option1']); ?></option>
                                    <option value="<?php echo htmlspecialchars($q['option2']); ?>">2. <?php echo htmlspecialchars($q['option2']); ?></option>
                                    <option value="<?php echo htmlspecialchars($q['option3']); ?>">3. <?php echo htmlspecialchars($q['option3']); ?></option>
                                    <option value="<?php echo htmlspecialchars($q['option4']); ?>">4. <?php echo htmlspecialchars($q['option4']); ?></option>
                                </select>
                                <button type="button" class="submit-answer" data-id="<?php echo $q['id']; ?>">解答を送信</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        
        <div id="output"></div>
        
        <!-- 問題一覧テーブル -->
        <div id="questions-table" style="display: none;">
            <h2>生成された問題</h2>
            <div id="questions-container"></div>
            <button type="button" id="show-answers" class="btn btn-primary">答えを表示</button>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        function toggleForms() {
            var selectedOption = $('input[name="q1"]:checked').val();
            if (selectedOption === "おはなし") {
                $("#text, #send").show();
                $("#upload-form, #upload-btn, #start-camera").hide();
            } else if (selectedOption === "テスト作成") {
                $("#text, #send").hide();
                $("#upload-form, #upload-btn, #start-camera").show();
            } else {
                $("#text, #send, #upload-form, #upload-btn, #start-camera").hide();
            }
        }

        // ラジオボタンの変更イベント
        $('input[name="q1"]').change(toggleForms);
        toggleForms();

        // 送信ボタンのクリックイベント
        $("#send").click(function(){
            var userText = $("#text").val();
            if(userText.trim() === "") {
                alert("入力してください");
                return;
            }

            // ユーザーの吹き出しを追加（右寄せ）
            $("#output").append('<div class="message" style="justify-content: flex-end;">' +
                                '<div class="user-message">' + userText + '</div></div>');
            $("#text").val("");

            if (userText.trim() === "答えを教えて") {
                $.ajax({
                    type: "POST",
                    url: "get_answers.php",
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.answer) {
                            $("#output").append('<div class="message"><div class="bot-message">' + response.answer + '</div></div>');
                        } else {
                            $("#output").append('<div class="message"><div class="bot-message error">回答が見つかりませんでした。</div></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("エラー:", error);
                        console.error("ステータス:", status);
                        console.error("レスポンス:", xhr.responseText);
                        $("#output").append('<div class="message"><div class="bot-message error">エラーが発生しました: ' + error + '</div></div>');
                    }
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: "chat_api.php",
                    data: { user_input: userText },
                    dataType: 'json',
                    success: function(response) {
                        console.log("受信したレスポンス:", response);
                        if (response && response.success && response.message) {
                            $("#output").append('<div class="message"><div class="bot-message">' + response.message + '</div></div>');
                        } else {
                            console.error("不正なレスポンス:", response);
                            $("#output").append('<div class="message"><div class="bot-message error">' + (response.message || 'エラーが発生しました') + '</div></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAXエラー:", error);
                        console.error("ステータス:", status);
                        console.error("レスポンス:", xhr.responseText);
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            $("#output").append('<div class="message"><div class="bot-message error">' + (errorResponse.message || error) + '</div></div>');
                        } catch (e) {
                            $("#output").append('<div class="message"><div class="bot-message error">エラーが発生しました: ' + error + '</div></div>');
                        }
                    }
                });
            }
        });

        // 画像アップロード時にDBへ保存
        $("#upload-btn").click(function(){
            var formData = new FormData();
            formData.append("image", $("#image-upload")[0].files[0]);
            
            $.ajax({
                type: "POST",
                url: "upload.php",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('アップロードレスポンス:', response);
                    if (response.success) {
                        $("#upload-status").text("画像アップロード成功");
                        $("#output").append('<div class="message"><div class="bot-message">画像のアップロードが完了しました。</div></div>');
                        
                        // 問題生成
                        $.post("generate_questions.php", { image_id: response.image_id }, function(qRes) {
                            console.log('問題生成レスポンス:', qRes);
                            if (qRes.success) {
                                $("#output").append('<div class="message"><div class="bot-message">問題の生成が完了しました。</div></div>');
                                // 3秒後にページをリロード
                                setTimeout(function() {
                                    location.reload();
                                }, 3000);
                            } else {
                                $("#output").append('<div class="message"><div class="bot-message error">問題の生成に失敗しました: ' + (qRes.message || qRes.error) + '</div></div>');
                            }
                        }).fail(function(xhr, status, error) {
                            console.error('問題生成エラー:', {xhr: xhr, status: status, error: error});
                            $("#output").append('<div class="message"><div class="bot-message error">問題の生成中にエラーが発生しました</div></div>');
                        });
                    } else {
                        console.error('アップロードエラー:', response);
                        $("#upload-status").text("画像アップロード失敗: " + (response.message || response.error || "不明なエラー"));
                        $("#output").append('<div class="message"><div class="bot-message error">画像のアップロードに失敗しました: ' + (response.message || response.error || "不明なエラー") + '</div></div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAXエラー:', {xhr: xhr, status: status, error: error});
                    $("#upload-status").text("画像アップロード失敗: " + error);
                    $("#output").append('<div class="message"><div class="bot-message error">画像のアップロードに失敗しました: ' + error + '</div></div>');
                }
            });
        });

        // 問題を表示する関数
        function displayQuestions(questions) {
            $("#output").append('<div class="message"><div class="bot-message">問題一覧</div></div>');
            
            // 問題一覧のテーブルを作成
            let tableHtml = '<table class="questions-table">';
            tableHtml += '<thead><tr><th>問題番号</th><th>問題文</th><th>選択肢</th></tr></thead><tbody>';
            
            questions.forEach((question, index) => {
                tableHtml += `<tr>
                    <td>${index + 1}</td>
                    <td>${question.question_text}</td>
                    <td>
                        <ol>
                            <li>${question.option1}</li>
                            <li>${question.option2}</li>
                            <li>${question.option3}</li>
                            <li>${question.option4}</li>
                        </ol>
                    </td>
                </tr>`;
            });
            
            tableHtml += '</tbody></table>';
            $("#output").append(tableHtml);
            
            // 問題一覧の後に解答入力フォームを表示
            displayAnswerForm(questions);
        }

        // 解答入力フォームを表示する関数
        function displayAnswerForm(questions) {
            let formHtml = '<div class="answer-form">';
            formHtml += '<h3>解答入力</h3>';
            formHtml += '<form id="answerForm">';
            
            questions.forEach((question, index) => {
                formHtml += `
                    <div class="question-answer">
                        <p>問題${index + 1}</p>
                        <select name="answer_${question.id}" class="answer-select">
                            <option value="">選択してください</option>
                            <option value="1">1. ${question.option1}</option>
                            <option value="2">2. ${question.option2}</option>
                            <option value="3">3. ${question.option3}</option>
                            <option value="4">4. ${question.option4}</option>
                        </select>
                    </div>`;
            });
            
            formHtml += '<button type="submit" class="submit-answers">解答を送信</button>';
            formHtml += '</form></div>';
            
            $("#output").append(formHtml);
            
            // フォーム送信時の処理
            $("#answerForm").on("submit", function(e) {
                e.preventDefault();
                submitAnswers(questions);
            });
        }

        // 解答を送信する関数
        function submitAnswers(questions) {
            let answers = {};
            questions.forEach(question => {
                answers[question.id] = $(`select[name="answer_${question.id}"]`).val();
            });
            
            $.ajax({
                url: 'submit_answers.php',
                type: 'POST',
                data: {
                    answers: answers
                },
                success: function(response) {
                    if (response.success) {
                        // 成功メッセージを表示
                        $("#output").append('<div class="message"><div class="bot-message">解答が保存されました。合計ポイント: ' + response.total_points + '点</div></div>');
                        
                        // 結果を表示
                        displayResults(response.results);
                        
                        // ページをリロードして最新の状態を表示
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        $("#output").append('<div class="message"><div class="bot-message error">' + (response.message || 'エラーが発生しました') + '</div></div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('通信エラー:', error);
                    $("#output").append('<div class="message"><div class="bot-message error">通信エラーが発生しました</div></div>');
                }
            });
        }

        // 結果を表示する関数
        function displayResults(results) {
            let resultsHtml = '<div class="results">';
            resultsHtml += '<h3>解答結果</h3>';
            
            results.forEach(result => {
                resultsHtml += `
                    <div class="result-item">
                        <p>問題${result.question_number}</p>
                        <p>あなたの回答: ${result.user_answer}</p>
                        <p>正解: ${result.correct_answer}</p>
                        <p>結果: ${result.is_correct ? '正解' : '不正解'}</p>
                        <p>獲得ポイント: ${result.points}点</p>
                        ${!result.is_correct ? `
                            <button class="report-error" data-question-id="${result.question_id}">
                                回答の誤りを指摘
                            </button>
                        ` : ''}
                    </div>`;
            });
            
            resultsHtml += '</div>';
            $("#output").append(resultsHtml);
        }

        // 解答ボタンのクリックイベント
        $('.answer-btn').click(function() {
            const questionId = $(this).data('question');
            const answer = $(this).data('answer');
            $('#answerInput' + questionId).val(answer);
            
            // 選択されたボタンのスタイルを変更
            $(this).siblings('.answer-btn').removeClass('selected');
            $(this).addClass('selected');
        });

        // 解答送信ボタンのクリックイベント
        $('.submit-answer').click(function() {
            const questionId = $(this).data('id');
            const answer = $(`#answer-${questionId}`).val();
            const questionContainer = $(this).closest('.question-container');

            if (!answer) {
                alert('解答を選択してください');
                return;
            }

            $.ajax({
                url: 'submit_answers.php',
                type: 'POST',
                data: {
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    if (response.success) {
                        // 成功した場合、問題全体を削除
                        questionContainer.fadeOut(300, function() {
                            $(this).remove();
                        });
                        alert('解答を送信しました。' + (response.points > 0 ? `\n獲得ポイント: ${response.points}点` : ''));
                        // 3秒後にページをリロード
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        alert('解答の送信に失敗しました: ' + response.message);
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
