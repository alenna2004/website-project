<?php require "functions.php" ?>

<?php
session_start();


// Check if user is a client
//if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'client') {
  //  die("You are not authorized to view this page.");
//}
$client_id = 1; //$_SESSION['user_id'];

$conn = connect_db();
if (!$conn) {
    die("Database connection failed");
}

$client_deposit_id = 1;
$amount = 60;
$fee_amount = 0;

// Determine if we should use transactions or not
$use_transactions = isset($_GET['transactions']) && $_GET['transactions'] === 'true';


if ($use_transactions){
    $sleep =$_GET['sleep'];
    $result = substract_operation($client_deposit_id, '-', $amount, $fee_amount,$sleep);
    echo "Withdrawal with transaction status: ";
    echo $sleep;
     if(is_string($result)){
         echo $result;
     } else {
         echo " Success";
     }
} else {
    $sleep =$_GET['sleep'];
     $result = substract_operation_without_transactions($client_deposit_id, '-', $amount, $fee_amount, $sleep);
     echo "Withdrawal without transaction status: ";
     echo $sleep;
      if(is_string($result)){
         echo $result;
     } else {
         echo " Success";
     }
}
mysqli_close($conn);
?>