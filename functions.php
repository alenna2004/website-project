<?php
require "config.php";

function connect_db(){
   $mysqli = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);
   if($mysqli->connect_errno != 0){
      return false;
   }else{
      return $mysqli;
   }
}


function get_type($formula)
{
     if ($formula == 0) {
        return "A";
    }else{
      return "B";
   }
}


function print_deposit_table($conn, $client_id = null) {

    $SQLstring = "SELECT * FROM deposit WHERE deposit.end_date >= CURDATE() AND deposit.begin_date <= CURDATE() ORDER BY deposit_id ASC";
     $result = $conn->query($SQLstring);
    if(!$result){
        return "Не удалось выполнить запрос " . $conn->error;
    }
    echo "<table class='deposits-table'>";
    echo '<thead><tr>
        <th>ID</th>
        <th>Название</th>
        <th>Валюта</th>
        <th>Продолжительность (месяцы)</th>
        <th>Тип</th>
        <th>Коммиссия</th>
        <th>Сумма*</th>
         <th>можно открыть с</th>
        <th>можно открыть по</th>';

    if ($client_id) {
           echo '<th>Действие</th>';
     }
        echo '</tr></thead>';

     echo "<tbody>";
    while ($row = $result->fetch_assoc())
    {
        echo '<tr>
            <td> '. $row["deposit_id"]. '</td>
             <td>' . htmlspecialchars($row["name"]).'</td>
             <td>'.htmlspecialchars($row["currency"]).'</td>
             <td> '.htmlspecialchars($row["duration"]). '</td>
              <td> '. get_type($row["formula"]). '</td>
               <td> '.htmlspecialchars($row["fee"]). '</td>
            <td> '.htmlspecialchars($row["min_start_amount"]). '</td>
             <td> '.htmlspecialchars($row["begin_date"]). '</td>
             <td> '.htmlspecialchars($row["end_date"]). '</td>';
            if ($client_id) {
                    echo '<td>';
                    echo '<form method="post" class="styled-form">';
                        echo '<input type="hidden" name="deposit_id" value="'. htmlspecialchars($row["deposit_id"]) .'">';
                        echo '<div class="form-group"><input type="number" name="start_amount" class="form-input" placeholder="Сумма вклада" required></div>';
                         echo '<button type="submit" class="button">Открыть вклад</button>';
                     echo '</form>';
                   echo '</td>';
            }
            echo '</tr>';
    }
    echo "</tbody></table>";
}

function open_client_deposit($conn, $client_id, $deposit_id, $money_amount) {
      $sql = "INSERT INTO client_deposit (client_id, deposit_id, opening_date, money_amount) VALUES (?, ?, CURDATE(), ?)";
      $stmt = $conn->prepare($sql);
       if(!$stmt){
           throw new Exception("Не удалось выполнить запрос: " . $conn->error);
       }
      $stmt->bind_param("iii", $client_id, $deposit_id, $money_amount);

      if (!$stmt->execute()) {
           throw new Exception("Ошибка вставки deposit: " . $stmt->error);
      }

      return true;
}



function login_user($conn, $user_type, $login, $password)
{
    $errors = [];
    if (empty($login) || empty($password) || empty($user_type)) {
            $errors[] = "Все поля должны быть заполнены";
        } else {

             $sql = "SELECT * FROM " . ($user_type === 'client' ? 'client' : 'worker') . " WHERE login = ?";
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("s", $login);
             $stmt->execute();
             $result = $stmt->get_result();

             if ($result->num_rows == 1) {
                 $user = $result->fetch_assoc();
                 if ($password == $user['password']) {
                    return true;
                 } else {
                     $errors[] = "Неверный пароль";
                    return $errors;
                 }
             } else {
                 $errors[] = "Неверный логин";
                  return $errors;
             }
    }
      return $errors;
}


function register_user($conn, $name, $surname, $login, $password, $phone_num, $email, $passport=null){
    $errors = [];
    if (empty($name) || empty($surname) || empty($login) || empty($password) ||  empty($phone_num) || empty($email) ) {
        $errors[] = "Заполните все поля отмеченные *";
    }

    if (strlen($password) < 6) {
        $errors[] = "Парль должен содержать не менее 6 символов";
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Неверный формат почты";
    }

       $sql_check = "SELECT * FROM client WHERE login = ?";
           $stmt_check = $conn->prepare($sql_check);
           $stmt_check->bind_param("s", $login);
           $stmt_check->execute();
           $result = $stmt_check->get_result();

           if ($result->num_rows > 0) {
               $errors[] = "Логин уже существует. Пожалуйста введите другой логин";
           }

       if (empty($errors)){
           $sql = "INSERT INTO client (name, surname, login, password, phone_num, email, passport) VALUES (?, ?, ?, ?, ?, ?, ?)";
           $stmt = $conn->prepare($sql);
           $stmt->bind_param("sssssss", $name, $surname, $login, $password, $phone_num, $email, $passport);
           if ($stmt->execute()) {
               return true;
           } else {
              $errors[] = "Возникла ошибка. Пожалуйста попытайтесь еще раз";
               return $errors;
           }
       }else{
          return $errors;
       }
}

function get_user_data($login, $user_type){
   $conn = connect_db();
   $sql = "SELECT * FROM " . ($user_type === 'client' ? 'client' : 'worker') . " WHERE login = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("s", $login);
   $stmt->execute();
   $result = $stmt->get_result();

   if($result->num_rows == 1){
      return $result->fetch_assoc();
   } else {
      return null;
   }
   mysqli_close($conn);
}

function update_user($user_login, $name, $surname, $phone_num, $email, $passport=null, ) {
    $conn = connect_db();

    $update_fields = ["name = ?", "surname = ?", "phone_num = ?", "email = ?","passport = ?"];
    $types = "sssss";
        $params = [$name, $surname, $phone_num, $email, $passport];

        $set_clause = implode(', ', $update_fields);

        $sql = "UPDATE client SET $set_clause WHERE login = ?";
        $stmt = $conn->prepare($sql);

        $types .= 's';
        $params[] = $user_login;
        $stmt->bind_param($types,...$params);

       if ($stmt->execute()) {
          return true;
       } else {
           return false;
      }
      mysqli_close($conn);
}


function submit_question($theme, $q_message, $user_id){
   $conn = connect_db();
    try {
      $sql = "INSERT INTO question (theme, q_message, client_id) VALUES (?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssi", $theme, $q_message, $user_id);
      if ($stmt->execute()) {
         return "Вопрос успешно отправлен";
      } else {
         $errors[] = "Возникла ошибка. Пожалуйста попытайтесь еще раз";
         return $errors;
      }

    } catch (PDOException $e) {
        return "Возникла ошибка. Пожалуйста попытайтесь еще раз";
    }
    mysqli_close($conn);
}

function get_client_questions_with_answers($id){
   $conn = connect_db();
    try {
      $sql = "SELECT q.question_id, q.theme, q.q_message, q.status, a.a_message AS answer_message 
                           FROM question q 
                           LEFT JOIN answer a ON q.question_id = a.question_id 
                           WHERE q.client_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      return $result->fetch_all();
    } catch (PDOException $e) {
        return ["Не удалось выполнить запрос: " . $e->getMessage()];
    }
    mysqli_close($conn);
}


function get_unanswered_questions($conn){
     $sql = "SELECT question_id, theme, q_message, status FROM question WHERE status =0 || status=2";
        $result = $conn->query($sql);
    if($result){
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
           return ["Не удалось выполнить запрос: " . $conn->error];
        }

}

function update_question_status($conn, $question_id, $newStatus) {
    try {
        $sql = "UPDATE question SET status = ? WHERE question_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
             throw new Exception("Не удалось выполнить запрос: " . $conn->error);
        }

        $stmt->bind_param("ii", $newStatus, $question_id);

        if (!$stmt->execute()) {
             throw new Exception("Ошибка обновления: " . $stmt->error);
        }

        return true;

    } catch (Exception $e) {
        return "Ошибка выполнения: " . $e->getMessage();
    }
}
function insert_answer($conn, $question_id, $worker_id, $a_message) {
    try {
        $sql = "INSERT INTO answer (question_id, worker_id, a_message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if(!$stmt){
            throw new Exception("Не удалось выполнить запрос: " . $conn->error);
        }
        $stmt->bind_param("iis", $question_id, $worker_id, $a_message);

        if (!$stmt->execute()) {
            throw new Exception("Ошибка вставки answer: " . $stmt->error);
        }

        return true;

    } catch (Exception $e) {
        return "Ошибка выполнения: " . $e->getMessage();
    }
}

function get_question($conn, $question_id){
      $sql = "SELECT theme, q_message FROM question WHERE question_id = ?";
      $stmt = $conn->prepare($sql);
       if ($stmt){
            $stmt->bind_param("i", $question_id);
             $stmt->execute();
             $result = $stmt->get_result();
              if ($result){
                return $result->fetch_assoc();
             }else {
                  return "Ошибка выполнения запросы: " . $stmt->error;
             }
       }else {
           return "Не удалось выполнить запрос: " . $conn->error;
       }

}


function get_client_deposits($conn, $client_id) {
    $sql = "SELECT cd.client_deposit_id, d.name, d.currency, d.formula, d.per_cent, cd.opening_date, cd.money_amount, d.fee, d.end_date
            FROM client_deposit cd
            INNER JOIN deposit d ON cd.deposit_id = d.deposit_id
            WHERE cd.client_id = ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        return "Не удалось выполнить запрос: " . $conn->error;
    }
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if(!$result){
         return "Не удалось выполнить запрос: " . $stmt->error;
    }
    $deposits = [];
    while ($row = $result->fetch_assoc()) {
        $deposits[] = $row;
    }
    return $deposits;
}

function get_client_deposit_operations($conn, $client_deposit_id) {
    $sql = "SELECT operation_type, amount
            FROM operation
            WHERE client_deposit_id = ?";
    $stmt = $conn->prepare($sql);
     if(!$stmt){
          return "Не удалось выполнить запрос: " . $conn->error;
      }
    $stmt->bind_param("i", $client_deposit_id);
    $stmt->execute();
    $result = $stmt->get_result();
     if(!$result){
           return "Не удалось выполнить запрос: " . $stmt->error;
      }
    $operations = [];
    while ($row = $result->fetch_assoc()) {
        $operations[] = $row;
    }
    return $operations;
}

function get_client_deposit_by_id($conn, $client_deposit_id){
     $sql = "SELECT cd.client_deposit_id, d.formula, d.fee, cd.opening_date, d.end_date, cd.money_amount
             FROM client_deposit cd
             INNER JOIN deposit d ON cd.deposit_id = d.deposit_id
             WHERE cd.client_deposit_id = ?";
     $stmt = $conn->prepare($sql);
   if(!$stmt){
          return "Не удалось выполнить запрос: " . $conn->error;
      }
     $stmt->bind_param("i", $client_deposit_id);
     $stmt->execute();
     $result = $stmt->get_result();
      if(!$result){
          return "Не удалось выполнить запрос: " . $stmt->error;
      }
     if ($row = $result->fetch_assoc()) {
        return $row;
      } else {
          return "Вклад" . $client_deposit_id ." not не найден";
     }
}

function add_operation($client_deposit_id, $operation_type, $amount) {
   $conn=connect_db();
    $conn->begin_transaction();
    try {
      $conn->query("LOCK TABLES client_deposit READ, operation WRITE");
        $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
         if(!$stmt){
            throw new Exception("Не удалось выполнить запрос: " . $conn->error);
         }
        $stmt->bind_param("isi", $client_deposit_id, $operation_type, $amount);
       if (!$stmt->execute()) {
            throw new Exception("Ошибка вставки " . $stmt->error);
         }
        $conn->commit();
        $conn->query("UNLOCK TABLES");
        return true;
    } catch (Exception $e) {
         $conn->rollback();
       if($conn->query("UNLOCK TABLES")){
           return "Ошибка при выполнении транзакции " . $e->getMessage();
       } else {
            return "Ошибка! Не удалось разблокировать таблицы " . $e->getMessage();
       }
    }
}

function substract_operation($client_deposit_id, $operation_type, $amount, $fee_amount) {
    $conn=connect_db();
    $conn->begin_transaction();
    try {

         $deposit = get_client_deposit_by_id($conn, $client_deposit_id);
         $conn->query("LOCK TABLES client_deposit READ, operation WRITE");
        if (is_string($deposit)){
            throw new Exception($deposit);
        }

         if($amount + $fee_amount > $deposit['money_amount']){
              throw new Exception("У вас недостаточно средств на счете");
        }

        $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
             throw new Exception("Ошибка подготовки запроса " . $conn->error);
        }
        $stmt->bind_param("isi", $client_deposit_id, $operation_type, $amount);
         if (!$stmt->execute()) {
              throw new Exception("Ошибка вставки " . $stmt->error);
        }

        if($fee_amount > 0){
             $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, 'f', ?)";
            $stmt = $conn->prepare($sql);
           if(!$stmt){
                  throw new Exception("Ошибка подготовки запроса " . $conn->error);
            }
            $stmt->bind_param("id", $client_deposit_id,  $fee_amount);
           if (!$stmt->execute()) {
                throw new Exception("Error inserting " . $stmt->error);
            }
         }

        $conn->commit();

        $conn->query("UNLOCK TABLES");
        return true;

    } catch (Exception $e) {
         $conn->rollback();
       if($conn->query("UNLOCK TABLES")){
           return "Ошибка при выполнении транзакции " . $e->getMessage();
       } else {
            return "Ошибка! Не удалось разблокировать таблицы " . $e->getMessage();
       }
    }
}


function get_theme() {
     if(isset($_COOKIE['theme'])){
         return $_COOKIE['theme'];
     } else {
          return "light"; // Default theme
     }

}


function set_theme($theme) {
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // Cookie expires in 30 days
}


 function get_all_client_deposits($conn) {
     $sql = "SELECT cd.client_deposit_id, c.name, c.surname, d.name AS deposit_name, cd.money_amount
             FROM client_deposit cd
             INNER JOIN client c ON cd.client_id = c.user_id
             INNER JOIN deposit d ON cd.deposit_id = d.deposit_id";
     $result = $conn->query($sql);

       if (!$result) {
             return "Не удалось выполнить запрос: " . $conn->error;
        }
     $deposits = [];
     while ($row = $result->fetch_assoc()) {
         $deposits[] = $row;
     }
     return $deposits;
 }

 function get_all_places($conn) {
     $sql = "SELECT * FROM place";
     $result = $conn->query($sql);
      if (!$result) {
             return "Не удалось выполнить запрос: " . $conn->error;
         }
     $places = [];
     while ($row = $result->fetch_assoc()) {
         $places[] = $row;
     }
     return $places;
 }
 function get_all_deposits_in_work($conn) {
      $sql = "SELECT * FROM deposit_in_work";
      $result = $conn->query($sql);
       if (!$result) {
             return "Не удалось выполнить запрос: " . $conn->error;
         }
     $deposit_in_work = [];
     while ($row = $result->fetch_assoc()) {
         $deposit_in_work[] = $row;
     }
      return $deposit_in_work;
 }

function add_deposit_in_work($conn, $worker_id, $client_deposit_id, $place_id) {
        $conn->begin_transaction();
         try {
            // Check if there is a record with that client_deposit_id.
            $conn->query("LOCK TABLE deposit_in_work WRITE");
            $sql = "SELECT * FROM deposit_in_work WHERE client_deposit_id = ?";
            $stmt = $conn->prepare($sql);
            if(!$stmt){
                throw new Exception("Не удалось выполнить запрос: " . $conn->error);
            }
             $stmt->bind_param("i", $client_deposit_id);
             $stmt->execute();
             $result = $stmt->get_result();

            if ($result->num_rows > 0) {
               throw new Exception("Вклад уже в работе");
           }

             $sql = "INSERT INTO deposit_in_work (worker_id, client_deposit_id, place_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
             if(!$stmt){
                  throw new Exception("Не удалось выполнить запрос: " . $conn->error);
            }
           $stmt->bind_param("iii", $worker_id, $client_deposit_id, $place_id);
             if (!$stmt->execute()) {
                 throw new Exception("Ошибка вставки " . $stmt->error);
            }
            $conn->commit();
            $conn->query("UNLOCK TABLES");
        return true;
    } catch (Exception $e) {
         $conn->rollback();
       if($conn->query("UNLOCK TABLES")){
           return "Ошибка при выполнении транзакции " . $e->getMessage();
       } else {
            return "Ошибка! Не удалось разблокировать таблицы " . $e->getMessage();
       }
    }
    }


function get_deposit_by_id($conn, $deposit_id){
    $sql = "SELECT * FROM deposit WHERE deposit_id = ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        return "Не удалось выполнить запрос: " . $conn->error;
    }
    $stmt->bind_param("i", $deposit_id);
    $stmt->execute();
    $result = $stmt->get_result();
     if ($row = $result->fetch_assoc()){
          return $row;
     } else {
         return "Вклад " . $deposit_id . " не найден";
     }
}


function update_client_deposit_sum($conn) {
    $updated_count = 0;
     $sql = "SELECT
             cd.client_deposit_id,
              cd.opening_date,
               cd.money_amount,
              d.formula,
              d.per_cent,
              d.duration
         FROM client_deposit cd
          INNER JOIN deposit d ON cd.deposit_id = d.deposit_id";
    $result = $conn->query($sql);
     if(!$result){
         return "Error getting result: " . $conn->error;
      }

    while ($row = $result->fetch_assoc()) {
         $client_deposit_id = $row['client_deposit_id'];
        $opening_date = new DateTime($row['opening_date']);
         $duration_months = (int) $row['duration'];
         $formula = (int) $row['formula'];
       $per_cent = (float) $row['per_cent'];
        $money_amount = (int) $row['money_amount'];
      $today = new DateTime();
      $today->setTime(0,0,0);
      $opening_date->setTime(0,0,0);

        $target_date = clone $opening_date;
      $target_date->modify("+$duration_months months");
      $target_date_year = $target_date->format('Y');
       $target_date_month = $target_date->format('m');
      $today_year = $today->format('Y');
      $today_month = $today->format('m');

       if($formula == 0){
          if ($today_year == $target_date_year && $today_month == $target_date_month) {
             $interest = $money_amount * $per_cent / 100;
             if(add_operation($conn, $client_deposit_id, '+', $interest, 'interest')){
                  $updated_count++;
              }
            }
       } else if($formula == 1){
           if (($today_year < $target_date_year) || ($today_year == $target_date_year && $today_month <= $target_date_month)) {
            $interest = $money_amount * $per_cent / 100;
            if(add_operation($conn, $client_deposit_id, '+', $interest, 'interest')){
                $updated_count++;
                }
            }
        }
    }
    return $updated_count;
}
?>