<?php require "functions.php" ?>
<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
     if(isset($_POST["theme_switch"])) {
          $new = $_POST["theme_switch"];
          set_theme($new);
          header("Location: ".$_SERVER['PHP_SELF']);
      }
 }
 $current = get_theme();
// Create MySQLi connection
$conn = connect_db();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$questionId = isset($_GET['question_id']) ? intval($_GET['question_id']) : null;


if (!$questionId || $questionId <= 0) {
  echo "Вопрос не найден";
    $conn->close();
    exit;
}

try {
    // 1. Fetch question data.
    $sql = "SELECT theme, q_message FROM question WHERE question_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $questionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result){
         echo "Ошибка выполнения запроса";
         $stmt->close();
        $conn->close();
        exit;
    }
    $question = $result->fetch_assoc();

      if (!$question){
        echo "Вопрос не найден";
         $stmt->close();
        $conn->close();
        exit;
      }


    // 2. Fetch the latest answer (if any).
     $sql = "SELECT a_message FROM answer WHERE question_id = ? ORDER BY answer_id DESC LIMIT 1";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param('i', $questionId);
    $stmt->execute();
    $result = $stmt->get_result();

      if (!$result) {
        echo "Ошибка выполнения запроса";
        $stmt->close();
         $conn->close();
         exit;
      }
    $answer = $result->fetch_assoc();
    $a_message = ($answer && isset($answer['a_message'])) ? $answer['a_message'] : '';


    // 3. Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $answerMessage = isset($_POST['answer']) ? $_POST['answer'] : '';
         if (empty($answerMessage)) {
             echo "Пожалуйста введите ответ";
              $conn->close();
              exit;
         }

        $sql = "UPDATE answer SET a_message = ? WHERE question_id = ? ORDER BY answer_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $answerMessage, $questionId);
         if(!$stmt->execute()){
             echo "Не удалось добавить ответ";
             $conn->close();
              exit;
         }


        $sql = "UPDATE question SET status = 1 WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $questionId);
        if(!$stmt->execute()){
              echo "Не удалось обновить статус";
            $conn->close();
             exit;
        }


        header("Location: index.php");
         $conn->close();
        exit();
    }

}   catch (mysqli_sql_exception $e) {
         echo "Error: " . $e->getMessage();
}
    catch (Exception $e)
{
    echo "Error: " . $e->getMessage();
} finally {
    // Close the connection
     if (isset($stmt)) {
        $stmt->close();
    }
    if(isset($conn)){
     $conn->close();
   }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ответить</title>
       <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
</head>
<body>
    <div class="container">
        <main>
            <div class="profile-info">
                <h2>Ответить</h2>
                <div class="form-content">
                   <p class="form-label"><strong>Тема:</strong> <?php echo htmlspecialchars($question['theme'] ?? ''); ?></p>
                    <p class="form-label"><strong>Вопрос:</strong> <?php echo htmlspecialchars($question['q_message'] ?? ''); ?></p>
                    <form method="post" action="" class="styled-form">
                         <div class="form-group">
                            <label for="answer" class="form-label">Ответ:</label>
                             <textarea id="answer" name="answer" rows="5" cols="40" class="form-input" required><?php echo htmlspecialchars($a_message); ?></textarea>
                        </div>
                            <button type="submit" class="button">Отправить</button>
                     </form>
                    <div class="back-link">
                       <a href="answer_form.php">Назад к списку вопросов</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
  <?php include "footer.php" ?>
   <input type="hidden" id="timeout" value="<?php echo 120; ?>">
   <?php
   if (isset($_SESSION['user_login'])) {
       echo '<script src="timer.js"></script>';
   }?>
   <script src="script.js"></script></body>
</html>