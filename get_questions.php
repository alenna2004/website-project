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
    echo json_encode(['error' => 'Не удалось подключиться к базе данных ' . $conn->connect_error]);
    exit;
}

// Prepare and execute the SQL query
try {
    $sql = "
      SELECT q.question_id, q.theme, q.q_message, q.status, a.worker_id
      FROM question q LEFT JOIN answer a ON q.question_id = a.question_id
      WHERE a.answer_id = (SELECT MAX(answer_id) from answer an WHERE an.question_id = q.question_id) OR a.answer_id IS NULL
        ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
       http_response_code(500);
       echo json_encode(['error' => "Не удалось выполнить запрос"]);
        $stmt->close();
        $conn->close();
      exit;
    }

    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    // Send JSON response
    echo json_encode($questions);


} catch (mysqli_sql_exception $e) {
    // Handle database-related errors
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка' . $e->getMessage()]);

} catch (Exception $e) {
     //Catch other possible errors
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка' . $e->getMessage()]);
}
 finally {
    // Close the connection
      $conn->close();
}
?>