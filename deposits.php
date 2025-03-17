<?php require "functions.php" ?>
<?php
session_start();

// Check if user is a worker
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'worker') {
    die("Вы не авторизованы");
}
$worker_id = $_SESSION['user_id'];

$conn = connect_db();
if (!$conn) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
     if(isset($_POST["theme_switch"])) {
          $new = $_POST["theme_switch"];
          set_theme($new);
          header("Location: ".$_SERVER['PHP_SELF']);
      }
 }
 $current = get_theme();


$message = "";

$deposits = get_all_client_deposits($conn);
if(is_string($deposits)){
    $message = $deposits;
}
$places = get_all_places($conn);
if(is_string($places)){
    $message = $places;
}
$deposits_in_work = get_all_deposits_in_work($conn);
if(is_string($deposits_in_work)){
   $message = $deposits_in_work;
}
// Transform $deposits_in_work into array with the key as client_deposit_id for quick access
$deposits_in_work_by_id = array();

foreach ($deposits_in_work as $deposit_in_work) {
    $deposits_in_work_by_id[$deposit_in_work['client_deposit_id']] = $deposit_in_work;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['client_deposit_id']) && isset($_POST['place_id'])) {
       $client_deposit_id = (int)$_POST['client_deposit_id'];
        $place_id = (int)$_POST['place_id'];


        if (isset($deposits_in_work_by_id[$client_deposit_id])) {
             $message = "Вклад уже в работе";
        } else {
           try{
              if(add_deposit_in_work($conn, $worker_id, $client_deposit_id, $place_id)){
                 $message = "Вклад добавлен в работу";
                $deposits_in_work = get_all_deposits_in_work($conn);
                if(is_string($deposits_in_work)){
                     $message = $deposits_in_work;
                }
                    // Transform $deposits_in_work into array with the key as client_deposit_id for quick access
                 $deposits_in_work_by_id = array();

                foreach ($deposits_in_work as $deposit_in_work) {
                      $deposits_in_work_by_id[$deposit_in_work['client_deposit_id']] = $deposit_in_work;
                }
            }
          }  catch(Exception $e){
            $message = $e->getMessage();
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
    <title>Назначение вкладов на работу</title>
    <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
</head>
<body id='dep'>
<?php include 'nav.php'; ?>
    <div class="container">
      <main>
             <h2>Назначение вкладов на работу</h2>
               <?php if($message) {
                    echo "<p class=\"error\">$message</p>";
                }?>
                 <table class="deposits-table">
                      <thead>
                        <tr>
                           <th>ID вклада</th>
                            <th>Клиент</th>
                             <th>Название вклада</th>
                            <th>Сумма вклада</th>
                            <th>Действие</th>
                        </tr>
                      </thead>
                      <tbody>
                          <?php if ($deposits && !is_string($deposits)): ?>
                            <?php foreach ($deposits as $deposit): ?>
                                  <tr>
                                   <td><?php echo htmlspecialchars($deposit['client_deposit_id']); ?></td>
                                    <td><?php echo htmlspecialchars($deposit['name'] . " " . $deposit['surname']); ?></td>
                                      <td><?php echo htmlspecialchars($deposit['deposit_name']); ?></td>
                                      <td><?php echo htmlspecialchars($deposit['money_amount']); ?></td>
                                        <td>
                                             <?php if (isset($deposits_in_work_by_id[$deposit['client_deposit_id']])): ?>
                                                     <span class="message">Вклад в работе</span>
                                                  <?php else: ?>
                                                        <form method="post" class="styled-form">
                                                                <input type="hidden" name="client_deposit_id" value="<?php echo $deposit['client_deposit_id']; ?>">
                                                                  <div class="form-group">
                                                                    <select name="place_id" class="form-input" required>
                                                                        <option value="" disabled selected>Выбрать место работы</option>
                                                                      <?php if ($places && !is_string($places)):?>
                                                                          <?php foreach($places as $place):?>
                                                                              <option value="<?php echo $place['place_id']; ?>"><?php echo htmlspecialchars($place['name']);?></option>
                                                                          <?php endforeach;?>
                                                                       <?php endif;?>
                                                                    </select>
                                                                  </div>
                                                            <button type="submit" class="button">Назначить</button>
                                                       </form>
                                                   <?php endif; ?>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                           <?php elseif (is_string($deposits)): ?>
                            <p><?=$deposits?></p>
                         <?php else: ?>
                           <p>Нет вкладов</p>
                           <?php endif; ?>
                      </tbody>
                 </table>
      </main>
      <?php include "footer.php" ?>
   <input type="hidden" id="timeout" value="<?php echo 120; ?>">
   <?php
   if (isset($_SESSION['user_login'])) {
       echo '<script src="timer.js"></script>';
   }?>
   <script src="script.js"></script>    </div>

</body>
</html>
