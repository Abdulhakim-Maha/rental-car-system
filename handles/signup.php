<?php
	require_once('../config/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';
    // $marketing_accept = isset($_POST['marketing_accept']) ? 1 : 0;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($password_confirmation)) {
        header("Location: ../signup.php?error=" . urlencode("Please fill in all required fields."));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../signup.php?error=" . urlencode("Invalid email format."));
        exit();
    }

    if ($password !== $password_confirmation) {
        header("Location: ../signup.php?error=" . urlencode("Passwords do not match."));
        exit();
    }

    // Optional: Add more validations (e.g., password strength, username format)

    // Check if email or username already exists
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
        $stmt->execute(['email' => $email, 'username' => $username]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            header("Location: ../signup.php?error=" . urlencode("Email or Username already exists."));
            exit();
        }
    } catch(PDOException $e) {
        header("Location: ../signup.php?error=" . urlencode("Database query failed: " . $e->getMessage()));
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    try {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash) VALUES (:first_name, :last_name, :username, :email, :password_hash)");
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'username' => $username,
            'email' => $email,
            'password_hash' => $hashed_password
        ]);

        // Redirect back with success message
        header("Location: ../signup.php?success=" . urlencode("Account created successfully. You can now log in."));
        exit();
    } catch(PDOException $e) {
        // Redirect back with error message
        header("Location: ../signup.php?error=" . urlencode("Registration failed: " . $e->getMessage()));
        exit();
    }
} else {
    // If not POST request, redirect back
    header("Location: ../signup.php");
    exit();
}
?>