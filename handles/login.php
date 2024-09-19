<?php
session_start();
require_once '../config/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Basic validation
    if (empty($identifier) || empty($password)) {
        header("Location: ../login.php?error=" . urlencode("Please fill in all required fields."));
        exit();
    }

    // Determine if the identifier is an email or username
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $field = 'email';
    } else {
        $field = 'username';
    }

    try {
        // Prepare a statement to fetch the user based on email or username
        $stmt = $conn->prepare("SELECT u.id, u.username, u.password_hash, r.name as role  FROM users u JOIN roles r ON u.role_id = r.id WHERE $field = :identifier LIMIT 1");
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct

                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Store user information in session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Handle "Remember Me" functionality
                if ($remember_me) {
                    // Generate a random token
                    $remember_token = bin2hex(random_bytes(16));

                    // Set a cookie that expires in 30 days
                    setcookie("remember_me", $remember_token, time() + (86400 * 30), "/", "", true, true); // Secure and HttpOnly flags

                }

                header("Location: ../index.php?success=" . urlencode("Logged in successfully."));
                exit();
            } else {
                // Password is incorrect
                header("Location: ../login.php?error=" . urlencode("Invalid credentials, incorrect password."));
                exit();
            }
        } else {
            // User not found
            header("Location: ../login.php?error=" . urlencode("Invalid credentials, don't have a user."));
            exit();
        }
    } catch(PDOException $e) {
        // Handle query errors
        header("Location: ../login.php?error=" . urlencode("Login failed: " . $e->getMessage()));
        exit();
    }
} else {
    // If not a POST request, redirect back to login page
    header("Location: ../login.php");
    exit();
}
?>