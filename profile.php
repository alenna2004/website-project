<?php require "functions.php" ?>
<?php
session_start();
if (!isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit();
}
   $userData = get_user_data($_SESSION['user_login'], $_SESSION['user_type']);
   if (!$userData) {
      echo "Пользователь не найден.";
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
   <meta charset="UTF-8">
   <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>

   <title>Профиль</title>
</head>
<body id="profile">
   <?php include "nav.php" ?>
   <div class="container">
   <main>

            <div class="profile-info">
                <h2>Профиль</h2>
                <p><strong>Логин:</strong> <?php echo htmlspecialchars($userData['login']); ?></p>
                 <p><strong>Имя:</strong> <?php echo htmlspecialchars($userData['name']); ?></p>
                <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($userData['surname']); ?></p>
                 <p><strong>Телефон:</strong> <?php echo htmlspecialchars($userData['phone_num']); ?></p>
                <p><strong>Почта:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
               <?php if ($_SESSION['user_type'] == 'client'): ?>
                    <p><strong>Пасспорт:</strong> <?php echo htmlspecialchars($userData['passport']); ?></p>
                    <a href="redact.php" class="button">Редактировать</a>
                <?php elseif ($_SESSION['user_type'] == 'worker'): ?>
                    <p><strong>Должность:</strong> <?php echo htmlspecialchars($userData['role']); ?></p>
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