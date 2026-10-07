<?php

session_start();

require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Email and password are required";

    } else {

        $sql = "SELECT id, name, password
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            $message = "User not found";

        } else {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];

                header("Location: tasks.php");
                exit;

            } else {

                $message = "Incorrect password";
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Login - TaskFlow</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    font-family: Arial, sans-serif;

    background: #f5f7fb;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;
}

.login-container {

    width: 100%;

    max-width: 420px;

}

.logo {

    text-align: center;

    font-size: 30px;

    font-weight: bold;

    margin-bottom: 8px;

    color: #111827;
}

.logo span {
    color: #2563eb;
}

.subtitle {

    text-align: center;

    color: #6b7280;

    margin-bottom: 25px;
}

.card {

    background: white;

    padding: 30px;

    border-radius: 14px;

    border: 1px solid #e5e7eb;
}

.card h1 {

    margin-top: 0;

    margin-bottom: 8px;

    color: #111827;
}

.card p {

    color: #6b7280;
}

.message {

    background: #fee2e2;

    color: #b91c1c;

    padding: 12px;

    border-radius: 7px;

    text-align: center;

    font-size: 14px;

    margin-bottom: 15px;
}

label {

    display: block;

    font-size: 14px;

    font-weight: bold;

    margin-top: 18px;

    margin-bottom: 7px;
}

input {

    width: 100%;

    padding: 13px;

    border: 1px solid #d1d5db;

    border-radius: 7px;

    font-size: 15px;

    outline: none;
}

input:focus {

    border-color: #2563eb;
}

button {

    width: 100%;

    padding: 13px;

    margin-top: 22px;

    border: none;

    border-radius: 7px;

    background: #2563eb;

    color: white;

    font-size: 15px;

    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.register {

    text-align: center;

    margin-top: 20px;

    font-size: 14px;

    color: #6b7280;
}

.register a {

    color: #2563eb;

    text-decoration: none;

    font-weight: bold;
}

.footer {

    text-align: center;

    color: #9ca3af;

    font-size: 13px;

    margin-top: 20px;
}

</style>

</head>

<body>

<div class="login-container">

<div class="logo">
Task<span>Flow</span>
</div>

<div class="subtitle">
Simple task management system
</div>


<div class="card">

<h1>
Welcome Back 👋
</h1>

<p>
Login to manage your tasks.
</p>


<?php if ($message !== ""): ?>

<div class="message">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>


<form method="POST">

<label>
Email
</label>

<input
type="email"
name="email"
placeholder="Enter your email"
required
>


<label>
Password
</label>

<input
type="password"
name="password"
placeholder="Enter your password"
required
>


<button type="submit">
Login
</button>

</form>


<div class="register">

Don't have an account?

<a href="register.php">
Create Account
</a>

</div>

</div>


<div class="footer">

TaskFlow © 2026

</div>

</div>

</body>

</html>