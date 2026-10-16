<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Allow login if password_verify succeeds OR plain text password matches
            $isValidPassword = false;
            if ($user) {
                if (password_verify($password, $user['Password']) || $password === $user['Password']) {
                    $isValidPassword = true;
                }
            }

            if ($user && $isValidPassword) {
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['full_name'] = $user['FullName'] ?? $user['Username'];
                $_SESSION['role'] = $user['Role'];

                // Cache user details locally in localStorage for future offline access
                echo "<script>
                    localStorage.setItem('eco_user', JSON.stringify({
                        id: " . json_encode($user['ID']) . ",
                        username: " . json_encode($user['Username']) . ",
                        full_name: " . json_encode($user['FullName'] ?? $user['Username']) . ",
                        role: " . json_encode($user['Role']) . "
                    }));
                    window.location.href = 'index.php';
                </script>";
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (Exception $e) {
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
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
    <h3 class="text-center fw-bold mb-4">ECO Cargo Login</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Offline Device Notice -->
    <div id="offlineNotice" class="alert alert-warning d-none text-center small mb-3" role="alert">
        <strong>Device Backup Mode:</strong> Click below to login with cached credentials.
    </div>

    <form method="POST" action="login.php" id="loginForm">
        <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" name="username" id="usernameInput" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" id="passwordInput" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mb-2">Login</button>
        <button type="button" class="btn btn-outline-secondary w-100 d-none" id="offlineLoginBtn" onclick="bypassOfflineLogin()">Offline Quick Login</button>
    </form>
</div>

<script>
    // Show cached offline login option if profile is stored locally
    window.addEventListener('DOMContentLoaded', () => {
        const cachedUser = localStorage.getItem('eco_user');
        if (cachedUser) {
            document.getElementById('offlineNotice').classList.remove('d-none');
            document.getElementById('offlineLoginBtn').classList.remove('d-none');
        }
    });

    function bypassOfflineLogin() {
        const cachedUser = localStorage.getItem('eco_user');
        if (cachedUser) {
            const user = JSON.parse(cachedUser);
            fetch('api_offline_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(user)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'index.php';
                } else {
                    window.location.href = 'index.php';
                }
            })
            .catch(() => {
                window.location.href = 'index.php';
            });
        }
    }
</script>

</body>
</html>