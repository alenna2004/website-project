<?php

// Database connection function (if you don't already have it)
function connect_db() {
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "demonstration";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        return "Database connection failed: " . $conn->connect_error;
    }

    return $conn;
}
function get_client_deposits($conn, $client_id) {
    $sql = "SELECT cd.client_deposit_id, d.name, d.currency, d.formula, d.per_cent, cd.opening_date, cd.money_amount, d.fee, d.end_date
            FROM client_deposit cd
            INNER JOIN deposit d ON cd.deposit_id = d.deposit_id
            WHERE cd.client_id = ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        return "Error preparing query: " . $conn->error;
    }
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
     $result = $stmt->get_result();
    if(!$result){
         return "Error getting result: " . $stmt->error;
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
          return "Error preparing query: " . $conn->error;
      }
    $stmt->bind_param("i", $client_deposit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if(!$result){
           return "Error getting result: " . $stmt->error;
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
          return "Error preparing query: " . $conn->error;
      }
     $stmt->bind_param("i", $client_deposit_id);
     $stmt->execute();
     $result = $stmt->get_result();
      if(!$result){
          return "Error getting result: " . $stmt->error;
      }
     if ($row = $result->fetch_assoc()) {
        return $row;
      } else {
          return "Deposit with id " . $client_deposit_id ." not found";
     }
}
function substract_operation($client_deposit_id, $operation_type, $amount, $fee_amount, $sleep) {
    $conn=connect_db();
    $conn->begin_transaction();
    try {

         $deposit = get_client_deposit_by_id($conn, $client_deposit_id);
         $conn->query("LOCK TABLES client_deposit READ, operation WRITE");
         sleep($sleep);
        if (is_string($deposit)){
            throw new Exception($deposit);
        }

         if($amount > $deposit['money_amount']){
              throw new Exception("Not enough funds in the deposit");
        }
        sleep($sleep);

        $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
             throw new Exception("Error preparing query: " . $conn->error);
        }
        $stmt->bind_param("isi", $client_deposit_id, $operation_type, $amount);
         if (!$stmt->execute()) {
              throw new Exception("Error inserting " . $stmt->error);
        }

        if($fee_amount > 0){
             $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, 'f', ?)";
            $stmt = $conn->prepare($sql);
           if(!$stmt){
                  throw new Exception("Error preparing query: " . $conn->error);
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
           return "Error during transaction: " . $e->getMessage();
       } else {
            return "Error during transaction, could not unlock tables: " . $e->getMessage();
       }
    }
}
function substract_operation_without_transactions($client_deposit_id, $operation_type, $amount, $fee_amount, $sleep) {
        $conn=connect_db();
       try {
        $deposit = get_client_deposit_by_id($conn, $client_deposit_id);

        if (is_string($deposit)){
            throw new Exception($deposit);
        }
         if($amount > $deposit['money_amount']){
              throw new Exception("Not enough funds in the deposit");
        }
        sleep($sleep);
            $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
           if(!$stmt){
                throw new Exception("Error preparing query: " . $conn->error);
            }
            $stmt->bind_param("isi", $client_deposit_id, $operation_type, $amount);
            if (!$stmt->execute()) {
               throw new Exception("Error inserting " . $stmt->error);
            }

            if($fee_amount > 0){
                $sql = "INSERT INTO operation (client_deposit_id, operation_type, amount) VALUES (?, 'f', ?)";
                $stmt = $conn->prepare($sql);
                if(!$stmt){
                    throw new Exception("Error preparing query: " . $conn->error);
                 }
              $stmt->bind_param("id", $client_deposit_id,  $fee_amount);
              if (!$stmt->execute()) {
                 throw new Exception("Error inserting " . $stmt->error);
               }
            }
           return true;
      } catch (Exception $e) {
           return "Error during transaction: " . $e->getMessage();
      }
}
?>