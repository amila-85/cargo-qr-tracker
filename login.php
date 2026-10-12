<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = :username OR username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $storedPassword = $user['Password'] ?? $user['password'] ?? '';

                if (password_verify($password, $storedPassword) || $password === $storedPassword) {
                    $_SESSION['user_id']   = $user['UserId'] ?? $user['id'] ?? 1;
                    $_SESSION['username']  = $user['Username'] ?? $user['username'] ?? $username;
                    $_SESSION['full_name'] = $user['FullName'] ?? $user['full_name'] ?? $user['Username'] ?? $username;
                    $_SESSION['role']      = $user['Role'] ?? $user['role'] ?? 'Staff'; // Load 'Admin' or 'Staff'

                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Username not found.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECO Cargo - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container" style="max-width: 400px;">
    <div class="card shadow-sm p-4">
        <h4 class="text-center fw-bold mb-3">ECO Cargo Login</h4>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger p-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
</body>
</html>