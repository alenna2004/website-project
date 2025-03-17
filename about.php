<?php require "functions.php" ?>

<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
     if(isset($_POST["theme_switch"])) {
          $new = $_POST["theme_switch"];
          set_theme($new);
          header("Location: ".$_SERVER['PHP_SELF']);
      }
 }
 $current = get_theme();

$conn = connect_db();
if (!$conn) {
    die("Не удалось подключиться к базе данных");
}
$message = "";
$client_id = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client') {
   $userData = get_user_data($_SESSION['user_login'], $_SESSION['user_type']);
   $passport = $userData['passport'];
   $client_id = $_SESSION['user_id'];
    if(!$passport){
           $message = "Заполните данные паспорта";
        }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
       if (isset($_POST["deposit_id"]) && isset($_POST["start_amount"])) {
           $deposit_id = (int)$_POST["deposit_id"];
           $start_amount = (int)$_POST["start_amount"];

           if(!$passport){
               $message = "Заполните данные паспорта";
          } else {
                $deposit = get_deposit_by_id($conn, $deposit_id);
              if(is_string($deposit)){
                 $message = $deposit;
             } else {
                 if ($start_amount < $deposit['min_start_amount']) {
                     $message = "Сумма вклада должна быть не меньше чем, чем минимальная сумма";
                 } else {
                      try {
                             if(open_client_deposit($conn, $client_id, $deposit_id, $start_amount)){
                                 $message = "Депозит успешно открыт";
                             }
                         } catch (Exception $e) {
                           $message = $e->getMessage();
                        }

                 }
             }
          }
        }
    }
}

mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
    <title>Банк</title>
</head>
<body id="about">
    <div class="container">
    <?php include "nav.php" ?>
       <?php if ($message) {
           echo "<p class='error'>$message</p>";
       }?>
    <div class="index-content"><p>*под суммой понимается минимальная сумма первого взноса</p></div>
       <?php
       $conn = connect_db();
       if(!$conn) {
           die("Не удалось подключиться к базе данных: " . $conn->error);
       }
       print_deposit_table($conn, $client_id);
       mysqli_close($conn);
       ?>
       <?php include "footer.php" ?>
      <input type="hidden" id="timeout" value="<?php echo 10; ?>">
      <?php
          if (isset($_SESSION['user_login'])) {
             echo '<script src="timer.js"></script>';
           }?>
        <script src="script.js"></script>
    </div>
</body>
</html>