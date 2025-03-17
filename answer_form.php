<?php require "functions.php" ?>
<?php
session_start();
if (!isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['user_type'] == 'client') {
    header("Location: index.php");
    exit();
}
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вопросы</title>
       <link rel="stylesheet" href=<?php echo ($current === "light") ? "styles.css" : "styles_2.css"; ?>>
</head>
<body id="answer">
   <?php include "nav.php" ?>
   <div class="container">
   <main>

<h2>Вопросы</h2>

<table id="questionTable" class="question-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Тема</th>
            <th>Вопрос</th>
            <th>Статус</th>
            <th>Ответить</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div id="loading" style="display:none;">Loading...</div>
<div id="error-message" style="color: red;"></div>
<script>
    const workerId = 1; // Replace with dynamic worker ID retrieval
    const questionTable = document.getElementById("questionTable");
    const loadingDiv = document.getElementById("loading");
    const errorMessageDiv = document.getElementById("error-message");

     function showLoading(){
        loadingDiv.style.display = "block";
        errorMessageDiv.style.display = "none"
     }
     function hideLoading(){
       loadingDiv.style.display = "none";
     }

    function updateTable() {

        fetch('get_questions.php')
            .then(response => {
              if(!response.ok){
                  throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json()
            })
            .then(data => {
                hideLoading()
                if (data.error) {
                  console.error("Не удалось выполнить запрос", data.error);
                  errorMessageDiv.textContent = "Не удалось загрузить данные";
                  errorMessageDiv.style.display = "block";
                  return;
                }
                errorMessageDiv.style.display = "none";
                const tbody = questionTable.querySelector('tbody');
                tbody.innerHTML = ''; // Clear existing rows

                data.forEach(question => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${question.question_id}</td>
                        <td>${question.theme}</td>
                        <td>${question.q_message}</td>
                        <td class="status-${question.status}">
                            ${question.status == 0 ? 'Ждет ответа' : question.status == 1 ? 'Обработан' : 'Обрабатывается'}
                        </td>
                        <td>
                           ${question.status === 0 ? `<button data-question-id="${question.question_id}" class="answer-button">Ответить</button>` :
                            (question.status === 2 && question.worker_id === workerId) ? `<button data-question-id="${question.question_id}" class="answer-button"> Продолжить</button>` : ''}
                        </td>
                    `;
                });

                //Event Listener
                document.querySelectorAll('.answer-button').forEach(button => {
                  button.addEventListener('click', handleButtonClick);
                });
            })
            .catch(error => {
              hideLoading()
              console.error('Fetch error:', error);
              errorMessageDiv.textContent = "Не удалось выполнить запрос";
               errorMessageDiv.style.display = "block";
            });
    }


   function handleButtonClick(event) {
         event.preventDefault();
         const button = event.target;
         const questionId = button.getAttribute('data-question-id');
         const row = button.closest('tr')
         const statusCell = row.querySelector('td:nth-child(4)'); // Select the 4th cell

          if (!questionId){
             errorMessageDiv.textContent = "Неверный id вопроса"
             errorMessageDiv.style.display = "block";
             return;
          }

         if(button.textContent === "Ответить") {
           showLoading();
             fetch('update_question.php', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ question_id: questionId, status: 2, worker_id: workerId }),
              })
             .then(response => {
               if(!response.ok){
                   throw new Error(`HTTP error! status: ${response.status}`);
               }
                return response.json()
             })
            .then(data => {
                hideLoading()
                if (data.success) {
                   updateTable();
               } else {
                   console.error("Error updating question:", data.message);
                   errorMessageDiv.textContent = "Не удалось обновить запрос";
                   errorMessageDiv.style.display = "block";
               }
            })
            .catch(error => {
               hideLoading();
               console.error("Error during fetch:", error);
                errorMessageDiv.textContent = "Не удалось обновить запрос";
                errorMessageDiv.style.display = "block";
            });
         }
          else {
              window.location.href = `answer.php?question_id=${questionId}`;
          }
    }


    updateTable();
    setInterval(updateTable, 2000);
</script>
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