<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Redirecting</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <script>
        // Function to start the countdown
        function startCountdown(seconds) {
            var countdownElement = document.getElementById("countdown");
            countdownElement.textContent = seconds;

            var countdownInterval = setInterval(function() {
                seconds--;
                countdownElement.textContent = seconds;

                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    redirectToLogin();
                }
            }, 1000);
        }

        // Function to redirect to login page
        function redirectToLogin() {
            window.location.href = "logout.php";
        }
    </script>
</head>
<body onload="startCountdown(3)">
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Redirecting...</h2>
        <p>Thank you for registering. You will be redirected to the login page shortly.</p>
        <p>Please wait <span id="countdown"></span> seconds...</p>
    </div>
</body>
</html>
