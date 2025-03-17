<?php require "functions.php" ?>
<?php
session_start();
if (isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit();
}
$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
     if(isset($_POST["theme_switch"])) {
          $new = $_POST["theme_switch"];
          set_theme($new);
          header("Location: ".$_SERVER['PHP_SELF']);
      }
 }
 $current = get_theme();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $conn = connect_db();

    $login = $_POST['login'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    $result = login_user($conn, $user_type, $login, $password);
      if ($result === true) {
            $_SESSION['user_login'] = $login;
            $_SESSION['user_type'] = $user_type;
            $userData = get_user_data($login, $user_type);
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['last_activity'] = time();
            header("Location: index.php");
             exit();
        } else{
            $errors = $result;
        }
        mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Войти</title>
    <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>

</head>
<body id='login'>
    <?php include "nav.php" ?>
    <div class="container">
    <div class="login-form">
    <h2>Вход</h2>
           <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <form action="login.php" method="post">
           <div class="form-field">
            <label for="login">Логин:</label>
            <input type="text" name="login" required>
          </div>
          <div class="form-field">
            <label for="password">Пароль:</label>
            <input type="password" name="password" required>
         </div>
          <div class="select-container">
            <label>Войти как:</label>
             <select name="user_type">
                   <option value="client">Пользователь</option>
                    <option value="worker">Работник</option>
             </select>
          </div>
          <div class="right"><a href="register.php">Зарегистрироваться</a></div>
            <br></br>
            <input type="submit" value="Войти">
        </form>
    </div>

<?php include "footer.php" ?>
<script src="script.js"></script>
</div>
</body>
</html>