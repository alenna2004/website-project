<?php require "functions.php" ?>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
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


$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $conn = connect_db();

    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone_num = $_POST['phone_num'];
    $email = $_POST['email'];
    $passport =  $_POST['passport'];
   if($password != $confirmPassword)
       $errors[] = "Пароли не совпадают";
    else{
        $result = register_user($conn, $name, $surname, $login, $password, $phone_num, $email, $passport);
         if ($result === true) {
            header("Location: login.php?registration=success");
             exit();
        } else{
            $errors = $result;
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
     <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>

</head>
<body id = 'login'>
    <?php include "nav.php" ?>
    <div class="container">
     <div class="registration-form">
        <h2>Регистрация</h2>
          <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <form action="register.php" method="post">
             <div class="form-field">
            <label for="name">*Имя:</label>
            <input type="text" name="name" required>
            </div>
            <div class="form-field">
            <label for="surname">*Фамилия:</label>
            <input type="text" name="surname" required>
           </div>
            <div class="form-field">
            <label for="login">*Логин:</label>
            <input type="text" name="login" required>
            </div>
            <div class="form-field">
            <label for="password">*Пароль:</label>
            <input type="password" name="password" required>
          </div>
            <div class="form-field">
            <label for="confirm_password">*Повторите пароль:</label>
            <input type="password" name="confirm_password" required>
           </div>
           <div class="form-field">
            <label for="phone_num">*Номер телефона:</label>
            <input type="tel" name="phone_num" required>
            </div>
             <div class="form-field">
            <label for="email">*Почта:</label>
             <input type="email" name="email" required>
           </div>
             <div id="passport-field" class="form-field">
                 <label for="passport">Серия и номер пасспорта:</label>
                <input type="text" name="passport">
            </div>
            <div class="right"><a  href="login.php">У меня уже есть аккаунт</a> </div>
            <br></br>
            <input type="submit" value="Зарегистрироваться">
        </form>
    </div>
<?php include "footer.php" ?>
</div>
</body>
</html>