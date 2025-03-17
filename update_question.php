<?php require "functions.php" ?>
<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Set headers for JSON response
header('Content-Type: application/json');

// Create MySQLi connection
$conn = connect_db();
// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Не удалось подключиться к базе данных ' . $conn->connect_error]);
    exit;
}


// Prepare and execute the SQL query
try {
    // 1. Get and decode request JSON data
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (!$requestData) {
      http_response_code(400); // Bad Request
      echo json_encode(['success' => false, 'message' => 'Неверный формат JSON']);
      exit;
    }

    // 2. Extract variables
    $questionId = isset($requestData['question_id']) ? intval($requestData['question_id']) : null;
    $status     = isset($requestData['status']) ? intval($requestData['status']) : null;
    $workerId   = isset($requestData['worker_id']) ? intval($requestData['worker_id']) : null;

    // 3. Validate variables
    if (!$questionId || !is_int($questionId) || $questionId <= 0 ||
         !is_int($status) || ($status < 0 || $status > 2) ||
        !is_int($workerId) || $workerId <= 0) {

        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Неверные параметры']);
        exit;
    }

    //4. Update question status
    $sqlUpdate = "UPDATE question SET status = ? WHERE question_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('ii', $status, $questionId); //bind parameters
    $stmtUpdate->execute();

    // Check for errors in update execution.
    if ($stmtUpdate->affected_rows === 0) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Не удалось обновить статус']);
        $stmtUpdate->close();
        $conn->close();
        exit;
    }

    // 5. Insert in answer if the status is 2
    if ($status == 2) {
         $sqlInsert = "INSERT INTO answer (question_id, worker_id, a_message) VALUES (?, ?, '')";
         $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param('ii', $questionId, $workerId); //bind parameters

        if (!$stmtInsert->execute()) {
             http_response_code(500); // Internal Server Error
             echo json_encode(['success' => false, 'message' => 'Не удалось добавить ответ']);
             $stmtInsert->close();
             $conn->close();
              exit;
        }
        $stmtInsert->close();
    }


    // Send successful response
    echo json_encode(['success' => true]);

}  catch (mysqli_sql_exception $e) {
        // Handle database-related errors
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => 'Ошибка' . $e->getMessage()]);
} catch (Exception $e) {
      //Catch other possible errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка' . $e->getMessage()]);
}
 finally {
    // Close the statement and connection
    if (isset($stmtUpdate)) {
        $stmtUpdate->close();
    }
      $conn->close();

}

?>