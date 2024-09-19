<?php
// approve.php

// Start the session to access session variables
session_start();

// Include the navigation bar


// Include the database connection file
require_once 'config/connect.php';

// Check if the user is logged in and has the 'Supervisor' or 'Director' role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Supervisor', 'Director'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Please log in as Supervisor or Director to approve requests."));
    exit();
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if 'id' parameter is present and is a positive integer
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    header("Location: index.php?error=" . urlencode("Invalid car request ID."));
    exit();
}

$request_id = (int) $_GET['id'];

// Initialize variables for messages
$error = '';
$success = '';

// Handle form submission for approval
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
    } else {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Fetch the current status of the request
            $stmt = $conn->prepare("
                SELECT 
                    cr.id,
                    rs.name AS status_name
                FROM 
                    carrequests cr
                INNER JOIN 
                    requeststatus rs ON cr.status_id = rs.id
                WHERE 
                    cr.id = :request_id
                FOR UPDATE
            ");
            $stmt->execute(['request_id' => $request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                throw new Exception("Car request not found.");
            }

            $current_status = $request['status_name'];

            // Define the status progression
            $status_progression = [
                'Supervisor Approved' => 'Director Approved',
                'Director Approved' => 'Approved'
            ];

            if (!array_key_exists($current_status, $status_progression)) {
                throw new Exception("Invalid status for approval. Current status: {$current_status}");
            }

            $new_status = $status_progression[$current_status];

            // Fetch the status_id for the new status
            $status_stmt = $conn->prepare("SELECT id FROM requeststatus WHERE name = :status_name LIMIT 1");
            $status_stmt->execute(['status_name' => $new_status]);
            $status_row = $status_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$status_row) {
                throw new Exception("New status '{$new_status}' not found in requeststatus table.");
            }

            $new_status_id = $status_row['id'];

            // Update the car request's status
            $update_stmt = $conn->prepare("
                UPDATE carrequests 
                SET status_id = :new_status_id, updated_at = CURRENT_TIMESTAMP
                WHERE id = :request_id
            ");
            $update_stmt->execute([
                'new_status_id' => $new_status_id,
                'request_id' => $request_id
            ]);

            // Optionally, log the approval action (e.g., in an audit table)
            // Uncomment and adjust the following lines if you have an audit table
            /*
            $audit_stmt = $conn->prepare("
                INSERT INTO request_audit (request_id, action, performed_by, performed_at)
                VALUES (:request_id, :action, :performed_by, CURRENT_TIMESTAMP)
            ");
            $audit_stmt->execute([
                'request_id' => $request_id,
                'action' => "Status changed from '{$current_status}' to '{$new_status}'",
                'performed_by' => $_SESSION['user_id']
            ]);
            */

            // Commit the transaction
            $conn->commit();

            $success = "Car request status has been updated to '{$new_status}'.";

        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollBack();
            error_log("Error in approve.php: " . $e->getMessage());
            $error = "Error updating the car request status. Please try again later.";
        }
    }
}

// Fetch the specific car request details
try {
    $stmt = $conn->prepare("
        SELECT 
            cr.id,
            cr.title,
            cr.first_name,
            cr.last_name,
            cr.position,
            cr.user_id,
            cr.car_id,
            cr.purpose,
            cr.request_date,
            cr.destination,
            cr.subdistrict,
            cr.district,
            cr.province,
            cr.departure_time,
            cr.return_time,
            cr.manday,
            cr.driver_name,
            cr.oil_expense,
            cr.total_distance,
            cr.remarks,
            rs.name AS status_name,
            cr.created_at,
            cr.updated_at
        FROM 
            carrequests cr
        INNER JOIN 
            requeststatus rs ON cr.status_id = rs.id
        WHERE 
            cr.id = :request_id
        LIMIT 1
    ");
    $stmt->execute(['request_id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($request['car_id'] === NULL) {
		$request['category'] = NUll;
		$request['usage_type'] = NUll;
		$request['registration_type'] = NUll;
		$request['make'] = NUll;
		$request['model'] = NUll;
		$request['license_plate'] = NUll;
	} else {
		 $stmt = $conn->prepare("
			SELECT 
				*
			FROM 
				cardetails_view 
			WHERE 
				id = :car_id
			LIMIT 1
    	");
    	$stmt->execute(['car_id' => $request['car_id']]);
   	 	$request_view = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$request) {
			header("Location: index.php?error=" . urlencode("cannot query cardetails view"));
			exit();
		}
		$request['category'] = $request_view['category'];
		$request['usage_type'] = $request_view['usage_type'];
		$request['registration_type'] = $request_view['registration_type'];
		$request['make'] = $request_view['make'];
		$request['model'] = $request_view['model'];
		$request['license_plate'] = $request_view['license_plate'];
		
	}
    if (!$request) {
        header("Location: index.php?error=" . urlencode("Car request not found."));
        exit();
    }
} catch (PDOException $e) {
    // Log the detailed error message
    error_log("Database error while fetching car request: " . $e->getMessage());

    header("Location: index.php?error=" . urlencode("Database error. Please try again later."));
    exit();
}

// Define the stages in order
$stages = ['Requested', 'Head Assigned', 'Supervisor Approved', 'Director Approved', 'Approved'];

// Determine the current stage index based on status_name
$current_stage_index = array_search($request['status_name'], $stages);

// If status_name is not found in stages, set to -1
if ($current_stage_index === false) {
    $current_stage_index = -1;
}