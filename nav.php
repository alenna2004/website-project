
<nav>
   <div class="brand">Мифический банк</div>
   <div class="links">
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};

if (isset($_SESSION['user_type'])) {
   if($_SESSION['user_type'] == 'worker'){
   echo '<a data-active="index" href="index.php">Главная</a>';
   echo   '<a data-active="dep" href="deposits.php">Работа со вкладами</a>';
   echo '<a data-active="answer" href="answer_form.php">Ответить на вопрос</a>';
   echo '<a data-active="profile" href="profile.php">Профиль</a>';
   echo   '<a data-active="logout" href="logout.php">Выйти</a>';
   } else {
      echo '<a data-active="index" href="index.php">Главная</a>';
      echo '<a data-active="about" href="about.php">О вкладах</a>';
      echo '<a data-active="my" href="mydeposits.php">Мои вклады</a>';
      echo '<a data-active="ask" href="ask.php">Задать вопрос</a>';
      echo '<a data-active="profile" href="profile.php">Профиль</a>';
      echo '<a data-active="logout" href="logout.php">Выйти</a>';
}
} else{
   echo '<a data-active="index" href="index.php">Главная</a>';
   echo   '<a data-active="about" href="about.php">О вкладах</a>';
   echo   '<a data-active="login" href="login.php">Войти</a>';
}
?>
<div class = "right">
 <form method="post">
   <?php
      $current = get_theme();
         $new = ($current === "light") ? "dark" : "light";
   ?>
   <button type="submit" name="theme_switch" value="<?php echo $new ?>" class="button">
   <?php echo ($current === "light") ? "Темная тема" : "Светлая тема"; ?>
      </button>
   </form>
</div>
</nav>



