<?php require "functions.php" ?>
<?php
session_start();
if (!isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['user_type'] == 'worker') {
    header("Location: index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
     if(isset($_POST["theme_switch"])) {
          $new = $_POST["theme_switch"];
          set_theme($new);
          header("Location: ".$_SERVER['PHP_SELF']);
      }
 }
$current = get_theme();

$client_id = $_SESSION['user_id'];
$message = "";

// Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $message = submit_question(htmlspecialchars($_POST['theme']), htmlspecialchars($_POST['q_message']), $client_id );
}

// Fetch Client's Questions with Answers
$questions = get_client_questions_with_answers($client_id);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Вопросы</title>
    <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
</head>
<body id="ask">
<?php include 'nav.php'; ?>

    <div class="container">
    <main>
      <div class="profile-info">
      <h2>Задать вопрос</h2>
      <?php
          if ($message) {
              echo "<p >$message</p>";
          }
      ?>
        <form method="post" action="">
            <div class="form-field">
              <label for="theme">Тема:</label>
              <input type="text" name="theme" required>
            </div>
            <div class="form-field">
              <label for="q_message">Вопрос:</label>
              <textarea name="q_message" rows="5" required></textarea>
            </div>
            <input type="submit" class ="button" value="Отправить">
        </form>
    </div>
    <br></br>
        <div class="profile-info">
            <h2>Ваши вопросы</h2>
            <?php if (count($questions) > 0 && !is_string($questions)): ?>
                <?php foreach ($questions as $question): ?>
                    <div class="question-item">
                        <br></br>
                        <strong>Тема:</strong> <?php echo htmlspecialchars($question['1']); ?><br>
                        <strong>Вопрос:</strong> <?php echo htmlspecialchars($question['2']); ?><br>
                        <?php if (!empty($question['4'])): ?>
                            <strong>Ответ:</strong> <?php echo htmlspecialchars($question['4']); ?><br>
                        <?php endif; ?>
                             <strong>Статус:</strong> <?php echo $question['3'] ? "Ответ получен" : "Ожидайте ответа"; ?>
                    </div>
                <?php endforeach; ?>
            <?php elseif (is_string($questions)): ?>
                <p><?=$questions?></p>
            <?php else: ?>
                <p>Вы еще не задали ни одного вопроса</p>
            <?php endif; ?>
        </div>
    </main>
    <?php include "footer.php" ?>
   <input type="hidden" id="timeout" value="<?php echo 120; ?>">
   <?php
   if (isset($_SESSION['user_login'])) {
       echo '<script src="timer.js"></script>';
   }?>
   <script src="script.js"></script>
</div>
</body>
</html>