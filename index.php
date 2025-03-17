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

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
    <title>Банк</title>
</head>
<body id="index">
    <?php include "nav.php" ?>
    <div class="container">
        <main>
            <div class="profile-info">
                <h2>Добро пожаловать в наш банк!</h2>
                <div class="index-content">
                  <p>Мы рады предложить своим клиентам выгодные условия по вкладам. У нас вы можете выбрать вариант вклада, который наилучшим образом подойдет под вашу финансовую стратегию.</p>

                    <p>У нас есть два типа вкладов: вклады без капитализации (тип А) и вклады с капитализацией (тип В). При вкладах типа А проценты начисляются один раз в конце срока, а при вкладах типа В проценты начисляются каждый месяц, что позволяет вам получать еще больший доход.</p>

                    <p>Для того, чтобы открыть вклад или задать дополнительные вопросы, вам необходимо зарегистрироваться. Наши специалисты всегда готовы помочь вам выбрать оптимальный вариант вклада и ответить на все ваши вопросы.</p>

                    <p>Мы ценим доверие наших клиентов и гарантируем надежное и конфиденциальное обслуживание в нашем банке. Доверьте свои финансы нам и наслаждайтесь стабильным ростом вашего капитала.</p>
                    <p>Присоединяйтесь к нашему банку и зарабатывайте с нами!</p>
                </div>
            </div>
        </main>
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