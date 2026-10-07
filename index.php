<?php

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: tasks.php");
    exit;
}

header("Location: login.php");
exit;

?>