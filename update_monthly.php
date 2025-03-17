<?php require "functions.php" ?>
<?php

$conn = connect_db();
if (!$conn) {
    die("Database connection failed");
}


$updated_deposits = update_client_deposit_sum($conn);
if(is_string($updated_deposits)){
    echo $updated_deposits;
} else{
    echo "Updated deposits: " . $updated_deposits;
}


mysqli_close($conn);
?>