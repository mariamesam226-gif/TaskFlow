<?php

session_start();

require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {

        $message = "All fields are required";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters";

    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO users (name, email, password)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $message = "Database error";

        } else {

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                header("Location: login.php");
                exit;

            } else {

                if ($stmt->errno === 1062) {

                    $message = "Email already exists";

                } else {

                    $message = "Registration failed";
                }
            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Create Account - TaskFlow</title>

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

.register-container {

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

.login {

    text-align: center;

    margin-top: 20px;

    font-size: 14px;

    color: #6b7280;
}

.login a {

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

<div class="register-container">

<div class="logo">
Task<span>Flow</span>
</div>

<div class="subtitle">
Create your account and organize your work
</div>


<div class="card">

<h1>
Create Account 🚀
</h1>

<p>
Start managing your tasks with TaskFlow.
</p>


<?php if ($message !== ""): ?>

<div class="message">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>


<form method="POST">

<label>
Full Name
</label>

<input
type="text"
name="name"
placeholder="Enter your full name"
required
>


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
placeholder="At least 6 characters"
required
>


<button type="submit">
Create Account
</button>

</form>


<div class="login">

Already have an account?

<a href="login.php">
Login
</a>

</div>

</div>


<div class="footer">

TaskFlow © 2026

</div>

</div>

</body>

</html>