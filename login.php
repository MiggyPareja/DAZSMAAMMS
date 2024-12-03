<?php
session_start();
require 'includes/db.php';

$max_attempts = 3;
$lockout_time = 10;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$time_since_last_attempt = time() - $_SESSION['last_attempt_time'];

if ($_SESSION['login_attempts'] >= $max_attempts && $time_since_last_attempt < $lockout_time) {
    $remaining_time = $lockout_time - $time_since_last_attempt;
    $error = "You failed to login 3 times. Please try again in $remaining_time seconds.";
} elseif ($_SESSION['login_attempts'] >= $max_attempts && $time_since_last_attempt >= $lockout_time) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
    unset($_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    if ($_SESSION['login_attempts'] < $max_attempts) {
        $username = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;

            header('Location: dashboard.php');
            exit();
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $attempts_left = $max_attempts - $_SESSION['login_attempts'];

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $error = "You failed to login 3 times. Please try again in $lockout_time seconds.";
            } else {
                $error = "The email or password you’ve entered is incorrect, attempts left: $attempts_left ";
            }

            // Store the error message in the session
            $_SESSION['error'] = $error;

            header('Location: login.php'); // Redirect without the error in the query string
            exit();
        }
    }
}

// Check for error message in the session and clear it after displaying
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @layer utilities {
            .bg-split {
                background: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 55%, #202092 45%), url('images/Background.png');
background-size: cover;
background-position: center;

            }
        }
    </style>
</head>
<body class="h-screen flex items-center justify-end bg-split">
    <div class="absolute left-10  top-24 text-white">
        <img src="images/DAZSMALOGO.png" alt="Logo" class="mb-4 w-48 h-48">
        <h1 class="text-5xl font-bold">DAZSMA-SAMS</h1>
        <p class="text-xl">School Assets Management System</p>
        <div class="mt-4">
    <p id="current-date"></p>
    <p id="current-time"></p>
</div>

<script>
    function formatDate(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    function formatTime(date) {
        const options = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true, weekday: 'long' };
        return date.toLocaleTimeString('en-US', options);
    }

    function updateDateTime() {
        const now = new Date();
        document.getElementById('current-date').innerText = formatDate(now);
        document.getElementById('current-time').innerText = formatTime(now);
    }

    updateDateTime();
</script>

        <div class="mt-8 flex space-x-4">
            <p class="text-sm underline cursor-pointer">HELP</p>
        </div>
    </div>
    <div class="w-full max-w-sm bg-white bg-opacity-20 backdrop-blur-md p-8 rounded-3xl mr-20">
            <h2 class="text-white text-2xl font-bold text-center mb-6">WELCOME</h2>
            <?php if (!empty($error))
            echo '<p class="error">' . $error . '</p>'; ?>
            <form action="login.php" method="POST">
                <div class="space-y-4">
                <?php if ($_SESSION['login_attempts'] < $max_attempts || $time_since_last_attempt >= $lockout_time): ?>       
                    <input type="text" name="email" placeholder="Email" class="w-full p-2 rounded-xl bg-gray-200">
                    <input type="password" name="password" placeholder="Password" class="w-full p-2 rounded-xl bg-gray-200">
                </div>

                <button class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                    LOG IN
                </button>
                <?php else: ?>
                
                <?php endif; ?>
                <p class="mt-4 text-center text-white">
                    <a href="register.php" class="underline">Create Account Here.</a>
                </p>
            </form>
        </div>
</body>
</html>


