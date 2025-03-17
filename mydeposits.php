<?php require "functions.php" ?>
<?php
session_start();

// Check if user is a client
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'client') {
    die("Вы не авторизованы");
}
$client_id = $_SESSION['user_id'];
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
$deposits = get_client_deposits($conn, $client_id);
if(is_string($deposits)){
    $message = $deposits;
}
$operations = array();

if ($deposits && !is_string($deposits)) {
    foreach ($deposits as $deposit) {
       $operations[$deposit['client_deposit_id']] = get_client_deposit_operations($conn, $deposit['client_deposit_id']);
       if(is_string($operations[$deposit['client_deposit_id']])){
             $message = $operations[$deposit['client_deposit_id']];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['operation_type']) && isset($_POST['amount']) && isset($_POST['client_deposit_id'])) {
         $operation_type = $_POST['operation_type'];
         $amount = (int)$_POST['amount'];
        $client_deposit_id = (int)$_POST['client_deposit_id'];

        if($amount <= 0){
           $message ="Amount must be greater than 0";
       } else {
             $deposit = get_client_deposit_by_id($conn, $client_deposit_id);
            if (is_string($deposit)){
                 $message = $deposit;
             } else {
                 $opening_date = new DateTime($deposit['opening_date']);
                 $now = new DateTime();
                 $deposit_end_date = new DateTime($deposit['end_date']);
                $deposit_formula = (int) $deposit['formula'];
               $deposit_fee = (float) $deposit['fee'];
                if($operation_type === "+"){
                    if($deposit_formula === 0){
                        $message = "Вы не можете выполнить эту операцию для этого вклада";
                    } else {
                        if($add = add_operation($client_deposit_id, $operation_type, $amount)){
                            if(is_string($add)){
                                $message = $add;
                            } else {
                                 $message = "Операция прошла успешно";
                                 header("Location: ".$_SERVER['PHP_SELF']);
                                  $operations[$client_deposit_id] = get_client_deposit_operations($conn, $client_deposit_id);
                                   if(is_string($operations[$client_deposit_id])){
                                       $message = $operations[$client_deposit_id];
                                     }
                             }
                         }

                     }
                  } else if ($operation_type === "-") {
                        $fee_amount = 0;
                        if($now < $deposit_end_date){
                            $fee_amount = $amount*$deposit_fee;
                        }
                     if ($substract = substract_operation($client_deposit_id, $operation_type, $amount, $fee_amount)) {
                          if(is_string($substract)){
                              $message = $substract;
                          } else{
                             $message = "Операция прошла успешно";
                             header("Location: ".$_SERVER['PHP_SELF']);
                              $operations[$client_deposit_id] = get_client_deposit_operations($conn, $client_deposit_id);
                               if(is_string($operations[$client_deposit_id])){
                                   $message = $operations[$client_deposit_id];
                               }
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
    <title>Мои вклады</title>
       <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
</head>
<body id= 'my'>
<?php include 'nav.php'; ?>
    <div class="container">
      <main>
        <div class="profile-info">
            <h2>Мои вклады</h2>
            <?php if($message) {
                echo "<p class=\"error\">$message</p>";
            }?>
              <?php if ($deposits && !is_string($deposits)): ?>
                  <?php foreach ($deposits as $deposit): ?>
                       <div class="deposit-item">
                            <h3><?php echo htmlspecialchars($deposit['name']); ?></h3>
                             <p><strong>Валюта:</strong> <?php echo htmlspecialchars($deposit['currency']); ?></p>
                            <p><strong>Дата открытия:</strong> <?php echo htmlspecialchars($deposit['opening_date']); ?></p>
                            <p><strong>Сумма вклада:</strong> <?php echo htmlspecialchars($deposit['money_amount']); ?></p>
                            <p><strong>Процентная ставка:</strong> <?php echo htmlspecialchars($deposit['per_cent']); ?>%</p>
                                    <p><strong>Формула вклада:</strong> <?php
                                    if ($deposit['formula'] == 0) {
                                        echo "Без капитализации";
                                    } else {
                                        echo "С капитализацией";
                                    }
                                    ?></p>

                        <h4>Операции</h4>
                           <?php if(isset($operations[$deposit['client_deposit_id']]) && is_array($operations[$deposit['client_deposit_id']]) && count($operations[$deposit['client_deposit_id']]) > 0):?>
                             <table class="operations-table">
                               <thead>
                                 <tr>
                                    <th>Тип операции</th>
                                    <th>Сумма</th>
                                     <th>Источник/Цель</th>
                                 </tr>
                                </thead>
                                 <tbody>
                                     <?php foreach ($operations[$deposit['client_deposit_id']] as $operation): ?>
                                        <tr>
                                             <td><?php
                                                if ($operation['operation_type'] == '+') {
                                                    echo "Пополнение";
                                                 } else if ($operation['operation_type'] == '-') {
                                                     echo "Снятие";
                                                 } else {
                                                   echo "Комиссия";
                                                 }
                                               ?></td>
                                            <td><?php echo htmlspecialchars($operation['amount']); ?></td>
                                            <td><?php
                                                 if ($operation['operation_type'] == '+') {
                                                     echo "Источник: Внешний счет";
                                                } else if ($operation['operation_type'] == '-') {
                                                     echo "Цель: Внешний счет";
                                                 } else {
                                                     echo "Комиссия";
                                                 }
                                            ?></td>
                                       </tr>
                                     <?php endforeach; ?>
                                </tbody>
                           </table>
                            <?php else :?>
                              <p>Нет операций по данному вкладу</p>
                            <?php endif;?>
                            <div class="operation-form">
                                <h4>Новая операция</h4>
                                <form method="post" action="" class="styled-form">
                                      <input type="hidden" name="client_deposit_id" value="<?php echo $deposit['client_deposit_id']; ?>">
                                        <div class="form-group">
                                               <label for="operation_type" class="form-label">Тип операции:</label>
                                                 <select id="operation_type" name="operation_type" class="form-input" required>
                                                   <option value="+" <?php if ($deposit['formula'] == 0) echo 'disabled'; ?>>Пополнение</option>
                                                     <option value="-">Снятие</option>
                                                </select>
                                            </div>
                                        <div class="form-group">
                                            <label for="amount" class="form-label">Сумма:</label>
                                                <input type="number" id="amount" name="amount" class="form-input" required>
                                        </div>
                                         <button type="submit" class="button">Выполнить</button>
                                </form>
                            </div>
                            <div class="separator"> </div>
                       </div>
                    
                   <?php endforeach; ?>
                <?php elseif (is_string($deposits)): ?>
                  <p><?=$deposits?></p>
                 <?php else: ?>
                     <p>У вас нет открытых вкладов</p>
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