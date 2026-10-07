<?php

session_start();

require_once "db.php";

/* Logout */
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* Check Login */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];

/* Add Task */
if (isset($_POST["add_task"])) {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($title !== "") {

        $sql = "INSERT INTO tasks (user_id, title, description)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iss",
            $user_id,
            $title,
            $description
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: tasks.php");
    exit;
}

/* Update Task */
if (isset($_POST["update_task"])) {

    $task_id = (int)($_POST["task_id"] ?? 0);
    $status = $_POST["status"] ?? "Pending";

    $allowed_statuses = [
        "Pending",
        "In Progress",
        "Completed"
    ];

    if (
        $task_id > 0 &&
        in_array($status, $allowed_statuses, true)
    ) {

        $sql = "UPDATE tasks
                SET status = ?
                WHERE id = ? AND user_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sii",
            $status,
            $task_id,
            $user_id
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: tasks.php");
    exit;
}

/* Delete Task */
if (isset($_POST["delete_task"])) {

    $task_id = (int)($_POST["task_id"] ?? 0);

    if ($task_id > 0) {

        $sql = "DELETE FROM tasks
                WHERE id = ? AND user_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ii",
            $task_id,
            $user_id
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: tasks.php");
    exit;
}

/* Get User Tasks */
$sql = "SELECT id, title, description, status, created_at
        FROM tasks
        WHERE user_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

/* Statistics */
$total_tasks = 0;
$pending_tasks = 0;
$progress_tasks = 0;
$completed_tasks = 0;

$tasks = [];

while ($task = $result->fetch_assoc()) {

    $tasks[] = $task;

    $total_tasks++;

    if ($task["status"] === "Pending") {
        $pending_tasks++;
    }

    if ($task["status"] === "In Progress") {
        $progress_tasks++;
    }

    if ($task["status"] === "Completed") {
        $completed_tasks++;
    }
}

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>TaskFlow Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #1f2937;
}

.navbar {
    background: #111827;
    color: white;
    padding: 18px 25px;
}

.navbar-content {
    max-width: 1100px;
    margin: auto;

    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 20px;
}

.logo {
    font-size: 22px;
    font-weight: bold;
}

.logo span {
    color: #60a5fa;
}

.logout {
    color: white;
    text-decoration: none;
    background: #ef4444;
    padding: 10px 16px;
    border-radius: 7px;
}

.container {
    max-width: 1100px;
    margin: auto;
    padding: 30px 20px;
}

.welcome {
    margin-bottom: 25px;
}

.welcome h1 {
    margin-bottom: 8px;
    font-size: 30px;
}

.welcome p {
    color: #6b7280;
    margin: 0;
}

/* Statistics */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 22px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.stat-title {
    color: #6b7280;
    font-size: 14px;
}

.stat-number {
    font-size: 30px;
    font-weight: bold;
    margin-top: 8px;
}

/* Main Grid */

.main-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 25px;
}

.card {
    background: white;
    border-radius: 12px;
    padding: 22px;
    border: 1px solid #e5e7eb;
}

.card h2 {
    margin-top: 0;
}

/* Form */

input,
textarea,
select {
    width: 100%;
    padding: 12px;

    border: 1px solid #d1d5db;
    border-radius: 7px;

    margin-top: 7px;
    margin-bottom: 15px;

    font-size: 15px;
}

textarea {
    min-height: 110px;
    resize: vertical;
}

label {
    font-size: 14px;
    font-weight: bold;
}

.add-button {
    width: 100%;

    padding: 12px;

    border: none;

    background: #2563eb;

    color: white;

    border-radius: 7px;

    font-size: 15px;

    cursor: pointer;
}

/* Tasks */

.tasks-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.task {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 15px;
}

.task-header {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.task h3 {
    margin: 0;
}

.task-description {
    color: #6b7280;
    line-height: 1.5;
}

.task-date {
    font-size: 13px;
    color: #9ca3af;
}

/* Status */

.status-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.status-form select {
    margin: 0;
    flex: 1;
}

.update-button {
    padding: 11px 15px;

    background: #111827;

    color: white;

    border: none;

    border-radius: 7px;

    cursor: pointer;
}

.delete-button {
    margin-top: 10px;

    padding: 9px 13px;

    background: #fee2e2;

    color: #b91c1c;

    border: none;

    border-radius: 7px;

    cursor: pointer;
}

/* Status Badge */

.badge {
    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}

.pending {
    background: #fef3c7;
    color: #92400e;
}

.progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.completed {
    background: #dcfce7;
    color: #166534;
}

.empty {
    text-align: center;
    color: #6b7280;
    padding: 30px;
}

/* Tablet */

@media (max-width: 800px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .main-grid {
        grid-template-columns: 1fr;
    }

}

/* Mobile */

@media (max-width: 500px) {

    .navbar-content {
        align-items: flex-start;
    }

    .welcome h1 {
        font-size: 24px;
    }

    .stats {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .stat-card {
        padding: 15px;
    }

    .stat-number {
        font-size: 24px;
    }

    .task-header {
        flex-direction: column;
    }

    .status-form {
        flex-direction: column;
        align-items: stretch;
    }

}

</style>

</head>

<body>

<nav class="navbar">

<div class="navbar-content">

<div class="logo">
Task<span>Flow</span>
</div>

<a
href="tasks.php?logout=1"
class="logout"
>
Logout
</a>

</div>

</nav>


<div class="container">

<div class="welcome">

<h1>
Welcome, <?= htmlspecialchars($user_name) ?> 👋
</h1>

<p>
Manage your tasks and keep your work organized.
</p>

</div>


<!-- Statistics -->

<div class="stats">

<div class="stat-card">

<div class="stat-title">
Total Tasks
</div>

<div class="stat-number">
<?= $total_tasks ?>
</div>

</div>


<div class="stat-card">

<div class="stat-title">
Pending
</div>

<div class="stat-number">
<?= $pending_tasks ?>
</div>

</div>


<div class="stat-card">

<div class="stat-title">
In Progress
</div>

<div class="stat-number">
<?= $progress_tasks ?>
</div>

</div>


<div class="stat-card">

<div class="stat-title">
Completed
</div>

<div class="stat-number">
<?= $completed_tasks ?>
</div>

</div>

</div>


<div class="main-grid">


<!-- Add Task -->

<div class="card">

<h2>
Add New Task
</h2>

<form method="POST">

<label>
Task Title
</label>

<input
type="text"
name="title"
placeholder="e.g. Learn PHP"
required
>

<label>
Description
</label>

<textarea
name="description"
placeholder="Write task details..."
></textarea>

<button
type="submit"
name="add_task"
class="add-button"
>
+ Add Task
</button>

</form>

</div>


<!-- Tasks -->

<div class="card">

<div class="tasks-title">

<h2>
My Tasks
</h2>

<strong>
<?= $total_tasks ?>
</strong>

</div>


<?php if (count($tasks) === 0): ?>

<div class="empty">

<p>
You don't have any tasks yet.
</p>

<p>
Add your first task from the form.
</p>

</div>

<?php endif; ?>


<?php foreach ($tasks as $task): ?>

<div class="task">

<div class="task-header">

<h3>
<?= htmlspecialchars($task["title"]) ?>
</h3>


<?php

$status_class = "pending";

if ($task["status"] === "In Progress") {
    $status_class = "progress";
}

if ($task["status"] === "Completed") {
    $status_class = "completed";
}

?>

<span class="badge <?= $status_class ?>">
<?= htmlspecialchars($task["status"]) ?>
</span>

</div>


<?php if ($task["description"] !== ""): ?>

<p class="task-description">
<?= htmlspecialchars($task["description"]) ?>
</p>

<?php endif; ?>


<p class="task-date">

Created:
<?= htmlspecialchars($task["created_at"]) ?>

</p>


<form
method="POST"
class="status-form"
>

<input
type="hidden"
name="task_id"
value="<?= $task["id"] ?>"
>

<select name="status">

<option
value="Pending"
<?= $task["status"] === "Pending" ? "selected" : "" ?>
>
Pending
</option>

<option
value="In Progress"
<?= $task["status"] === "In Progress" ? "selected" : "" ?>
>
In Progress
</option>

<option
value="Completed"
<?= $task["status"] === "Completed" ? "selected" : "" ?>
>
Completed
</option>

</select>

<button
type="submit"
name="update_task"
class="update-button"
>
Update
</button>

</form>


<form method="POST">

<input
type="hidden"
name="task_id"
value="<?= $task["id"] ?>"
>

<button
type="submit"
name="delete_task"
class="delete-button"
>
Delete Task
</button>

</form>

</div>

<?php endforeach; ?>


</div>

</div>

</div>

</body>

</html>

<?php

$conn->close();

?>