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
   $userData = get_user_data($_SESSION['user_login'], $_SESSION['user_type']);
   if (!$userData) {
      echo "Пользователь не найден";
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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone_num = $_POST['phone_num'];
    $email = $_POST['email'];
    $passport = isset($_POST['passport']) ? $_POST['passport'] : null;
    $updateResult = update_user($_SESSION['user_login'], $name, $surname, $phone_num, $email, $passport, $role);
    if($updateResult === true){
         header("Location: profile.php");
          exit();
       } else {
           $errors[] = "Ошибка. Попытайтесь еще раз";
       }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
   <meta charset="UTF-8">
   <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>

   <title>Редактирование</title>
</head>
<body id="profile">
   <?php include "nav.php" ?>
   <div class="container">
   <main>
            <div class="redact-form">
                <h2>Редактирование</h2>
                 <?php if (!empty($errors)): ?>
                        <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                         <?php endforeach; ?>
                        </div>
                <?php endif; ?>
                <form action="redact.php" method="post">
                    <div class="form-field">
                       <label for="name">Имя:</label>
                       <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                    </div>
                    <div class="form-field">
                         <label for="surname">Фамилия:</label>
                        <input type="text" name="surname" value="<?php echo htmlspecialchars($userData['surname']); ?>" required>
                    </div>
                   <div class="form-field">
                         <label for="phone_num">Телефон:</label>
                        <input type="tel" name="phone_num" value="<?php echo htmlspecialchars($userData['phone_num']); ?>" required>
                    </div>
                    <div class="form-field">
                         <label for="email">Почтa:</label>
                         <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                   </div>
                    <div class="form-field">
                        <label for="passport">Пасспорт:</label>
                        <input type="text" name="passport" value="<?php echo htmlspecialchars($userData['passport']); ?>">
                    </div>
                   <input type="submit" value="Сохранить" class="button">
                </form>
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