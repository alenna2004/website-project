let timeout = parseInt(document.getElementById('timeout').value);
 document.addEventListener('DOMContentLoaded', () => {
           let logoutTimer;
           const inactivityTime = timeout * 1000;

            const resetTimer = () => {
                clearTimeout(logoutTimer);
                logoutTimer = setTimeout(() => {
                  alert("Сессия завершена в связи с отсутствием активности");
                  window.location.href = 'logout.php';
              }, inactivityTime);
           };
            resetTimer();
           document.addEventListener('mousemove', resetTimer);
            document.addEventListener('keydown', resetTimer);
            document.addEventListener('click', resetTimer);
        });