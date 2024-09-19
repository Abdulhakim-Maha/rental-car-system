<?php
// handles/request_car.php

// Start the session to access session variables
session_start();

// Include the database connection file
require_once '../config/connect.php';

// Check if the user is logged in and has the 'Staff' role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=" . urlencode("Access denied."));
    exit();
}

// Ensure the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../requestcar.php");
    exit();
}

// Validate CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: ../requestcar.php?error=" . urlencode("Invalid CSRF token."));
    exit();
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Retrieve and sanitize form inputs
$title = sanitize_input($_POST['title'] ?? '');
$first_name = sanitize_input($_POST['first_name'] ?? '');
$last_name = sanitize_input($_POST['last_name'] ?? '');
$position = sanitize_input($_POST['position'] ?? '');
$purpose = sanitize_input($_POST['purpose'] ?? '');
$request_date = $_POST['request_date'] ?? '';
$destination = sanitize_input($_POST['destination'] ?? '');
$subdistrict = sanitize_input($_POST['subdistrict'] ?? '');
$district = sanitize_input($_POST['district'] ?? '');
$province = sanitize_input($_POST['province'] ?? '');
$departure_time = $_POST['departure_time'] ?? null;
$return_time = $_POST['return_time'] ?? null;
$manday = $_POST['manday'] ?? null;
$driver_name = sanitize_input($_POST['driver_name'] ?? '');
$oil_expense = $_POST['oil_expense'] ?? null;
$total_distance = $_POST['total_distance'] ?? null;
$remarks = sanitize_input($_POST['remarks'] ?? '');
$company = sanitize_input($_POST['company'] ?? '');
// Initialize an array to store error messages
$errors = [];

// Validate required fields
if (empty($title)) {
    $errors[] = "Title is required.";
}

if (empty($first_name)) {
    $errors[] = "First name is required.";
}

if (empty($last_name)) {
    $errors[] = "Last name is required.";
}

if (empty($position)) {
    $errors[] = "Position is required.";
}

if (empty($purpose)) {
    $errors[] = "Purpose is required.";
}

if (empty($request_date)) {
    $errors[] = "Request date is required.";
}

// Validate request_date format (YYYY-MM-DD)
$request_date_obj = DateTime::createFromFormat('Y-m-d', $request_date);
if (!$request_date_obj || $request_date_obj->format('Y-m-d') !== $request_date) {
    $errors[] = "Invalid request date format.";
}

// Validate departure_time format if provided (YYYY-MM-DDTHH:MM)
if ($departure_time) {
    echo $departure_time;
    $departure_time_obj = DateTime::createFromFormat('Y-m-d H:i:s', $departure_time);
    if (!$departure_time_obj || $departure_time_obj->format('Y-m-d H:i:s') !== $departure_time) {
        $errors[] = "Invalid departure time format.". $departure_time;
    }
} else {
    $departure_time_obj = null;
}

// Validate return_time format if provided (YYYY-MM-DDTHH:MM)
if ($return_time) {
    $return_time_obj = DateTime::createFromFormat('Y-m-d H:i:s', $return_time);
    if (!$return_time_obj || $return_time_obj->format('Y-m-d H:i:s') !== $return_time) {
        $errors[] = "Invalid return time format.";
    }
} else {
    $return_time_obj = null;
}

// Validate manday if provided (positive integer)
if ($manday !== null && $manday !== '') {
    if (!filter_var($manday, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $errors[] = "Manday must be a positive integer.";
    }
} else {
    $manday = null;
}

// Validate oil_expense if provided (positive decimal)
if ($oil_expense !== null && $oil_expense !== '') {
    if (!filter_var($oil_expense, FILTER_VALIDATE_FLOAT) || $oil_expense < 0) {
        $errors[] = "Oil expense must be a positive number.";
    }
} else {
    $oil_expense = null;
}

// Validate total_distance if provided (positive decimal)
if ($total_distance !== null && $total_distance !== '') {
    if (!filter_var($total_distance, FILTER_VALIDATE_FLOAT) || $total_distance < 0) {
        $errors[] = "Total distance must be a positive number.";
    }
} else {
    $total_distance = null;
}

// If there are validation errors, redirect back with errors
if (!empty($errors)) {
    // Combine all errors into a single string separated by commas
    $error_message = implode(" ", $errors);
    header("Location: ../requestcar.php?error=" . urlencode($error_message));
    exit();
}

// Fetch the status_id for 'Head Assigned' from the requeststatus table
try {
    $stmt = $conn->prepare("SELECT id FROM requeststatus WHERE name = :status_name LIMIT 1");
    $stmt->execute(['status_name' => 'Head Assigned']);
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$status) {
        // Log the error for debugging
        error_log("Head Assigned status not found in requeststatus table.");
        
        header("Location: ../requestcar.php?error=" . urlencode("Head Assigned status not found. Please contact the administrator."));
        exit();
    }
    $status_id = $status['id'];
} catch (PDOException $e) {
    // Log the detailed error message
    error_log("Database error while fetching status: " . $e->getMessage());
    
    header("Location: ../requestcar.php?error=" . urlencode("Database error. Please try again later."));
    exit();
}

// Insert the car request into the carrequests table
try {
    $stmt = $conn->prepare("
        INSERT INTO carrequests 
        (title, first_name, last_name, position, user_id, purpose, request_date, destination, subdistrict, district, province, departure_time, return_time, manday, driver_name, oil_expense, total_distance, remarks, status_id, company) 
        VALUES 
        (:title, :first_name, :last_name, :position, :user_id, :purpose, :request_date, :destination, :subdistrict, :district, :province, :departure_time, :return_time, :manday, :driver_name, :oil_expense, :total_distance, :remarks, :status_id, :company)
    ");
    
    $stmt->execute([
        'title' => $title,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'position' => $position,
        'user_id' => $_SESSION['user_id'],
        'purpose' => $purpose,
        'request_date' => $request_date_obj->format('Y-m-d'),
        'destination' => !empty($destination) ? $destination : null,
        'subdistrict' => !empty($subdistrict) ? $subdistrict : null,
        'district' => !empty($district) ? $district : null,
        'province' => !empty($province) ? $province : null,
        'departure_time' => $departure_time_obj ? $departure_time_obj->format('Y-m-d H:i:s') : null,
        'return_time' => $return_time_obj ? $return_time_obj->format('Y-m-d H:i:s') : null,
        'manday' => $manday,
        'driver_name' => !empty($driver_name) ? $driver_name : null,
        'oil_expense' => $oil_expense,
        'total_distance' => $total_distance,
        'remarks' => !empty($remarks) ? $remarks : null,
        'status_id' => $status_id,
        'company' => !empty($company) ? $company : null
    ]);
} catch (PDOException $e) {
    // Log the detailed error message
    error_log("Error inserting car request: " . $e->getMessage());
    
    header("Location: ../requestcar.php?error=" . urlencode("Error submitting your request. Please try again later."));
    exit();
}

// Optionally, you can handle the car_id assignment here if applicable
// Since car_id is to be assigned by the Head separately, we'll leave it as NULL

// Redirect back to requestcar.php with a success message
header("Location: ../index.php?success=" . urlencode("คุณได้สร้างคำร้องขอใช้ยานพาหนะเรียบร้อย"));
exit();
?>