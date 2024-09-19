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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
    } else {
        // Check the action
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'approve') {
            $action_performed = 'approve';
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
				header("Location: index.php?success=" . urlencode("อนุมัติเรียบร้อย"));
				exit();

			} catch (Exception $e) {
				// Rollback the transaction on error
				$conn->rollBack();
				error_log("Error in approve.php: " . $e->getMessage());
				$error = "Error updating the car request status. Please try again later.";
			}
        } elseif ($action === 'reject') {
            $action_performed = 'reject';
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

                // Allowed statuses for rejection
                $allowed_statuses = ['Supervisor Approved', 'Director Approved'];

                if (!in_array($current_status, $allowed_statuses)) {
                    throw new Exception("Invalid status for rejection. Current status: {$current_status}");
                }

                $new_status = 'Rejected';

                // Fetch the status_id for 'Rejected' status
                $status_stmt = $conn->prepare("SELECT id FROM requeststatus WHERE name = :status_name LIMIT 1");
                $status_stmt->execute(['status_name' => $new_status]);
                $status_row = $status_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$status_row) {
                    throw new Exception("Status '{$new_status}' not found in requeststatus table.");
                }

                $new_status_id = $status_row['id'];

                // Update the car request's status to 'Rejected'
                $update_stmt = $conn->prepare("
                    UPDATE carrequests 
                    SET status_id = :new_status_id, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :request_id
                ");
                $update_stmt->execute([
                    'new_status_id' => $new_status_id,
                    'request_id' => $request_id
                ]);

                // Commit the transaction
                $conn->commit();

                $success = "Car request status has been updated to '{$new_status}'.";
				header("Location: index.php?success=" . urlencode("ปฎิเสธเรียบร้อย"));
				exit();

            } catch (Exception $e) {
                // Rollback the transaction on error
                $conn->rollBack();
                error_log("Error in approve.php (rejection): " . $e->getMessage());
                $error = "Error updating the car request status. Please try again later.";
            }
        } else {
            $error = "Invalid action.";
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
            cr.updated_at,
			cr.company
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

function parseDate($org_date, $is_time = false) {
    // Array of Thai month names
    $thai_months = [
        1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    // Create a DateTime object
    $date = new DateTime($org_date);

    // Extract day, month, and year
    $day = $date->format('j');
    $month = (int)$date->format('n'); // Month as number without leading zeros
    $year = $date->format('Y');

    // Adjust year for Buddhist Era
    $year_be = $year + 543;

    // Format the date
    $formatted_date = "{$day} {$thai_months[$month]} {$year_be}";

    if ($is_time) {
        // Get time in 24-hour format
        $time = $date->format('H:i'); // 'H' is 24-hour format, 'i' is minutes
        $formatted_date .= " เวลา {$time}";
    }

    return $formatted_date;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>อนุมัติ</title>
	<!-- Tailwind CSS CDN -->
	<?php include "includes/font.php"; ?>
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- Include any additional scripts for modal functionality if needed -->
</head>

<body class="bg-gray-100">
	<?php include 'includes/nav.php'; ?>
	<section class="py-12">
		<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
			<!-- Breadcrumbs Navigation -->
			<nav aria-label="Breadcrumb" class="mb-8">
				<ol class="flex space-x-4">
					<?php foreach ($stages as $index => $stage): ?>
					<?php
                            // Determine the state of the stage
                            if ($index < $current_stage_index) {
                                // Completed stage
                                $state = 'completed';
                            } elseif ($index === $current_stage_index) {
                                // Current stage
                                $state = 'current';
                            } else {
                                // Upcoming stage
                                $state = 'upcoming';
                            }

                            // Set colors based on state
                            if ($state === 'completed') {
                                $circleColor = 'bg-green-500';
                                $textColor = 'text-gray-700';
                                $connectorColor = 'bg-green-500';
                            } elseif ($state === 'current') {
                                $circleColor = 'bg-blue-500';
                                $textColor = 'text-blue-700';
                                $connectorColor = 'bg-gray-300';
                            } else {
                                $circleColor = 'bg-gray-300';
                                $textColor = 'text-gray-500';
                                $connectorColor = 'bg-gray-300';
                            }
                        ?>
					<li class="flex items-center">
						<!-- Stage Circle -->
						<span
							class="flex items-center justify-center w-8 h-8 rounded-full <?php echo $circleColor; ?> text-white">
							<?php echo $index + 1; ?>
						</span>

						<!-- Stage Name -->
						<span class="ml-4 text-sm font-medium <?php echo $textColor; ?>">
							<?php
								switch ($stage) {
									case "Requested":
										echo "ส่งคำร้องขอ";
										break;
									case "Head Assigned":
										echo "รอหัวหน้างานยานพาหนะอนุมัติ";
										break;
									case "Supervisor Approved":
										echo "รอหัวหน้ากลุ่มงานบริหารทั่วไปอนุมัติ";
										break;
									case "Director Approved":
										echo "รอหัวผู้อำนวยการอนุมัติ";
										break;
									case "Approved":
										echo "อนุมัติ";
										break;
									// Add more cases for other stages if needed
									default:
										echo htmlspecialchars($stage);
								}
							?>
						</span>


						<!-- Connector -->
						<?php if ($index < count($stages) - 1): ?>
						<span class="mx-4 h-1 w-10 <?php echo $connectorColor; ?>"></span>
						<?php endif; ?>
					</li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<?php if ($success): ?>
			<div class="bg-white shadow-md rounded-lg p-8 mb-2">
				<h3 class="text-xl font-semibold text-gray-700 mb-4">
					<?php echo ($action_performed === 'approve') ? 'อนุมัติสำเร็จ' : 'ปฏิเสธสำเร็จ'; ?>
				</h3>
				<p class="text-gray-700">
					<?php echo ($action_performed === 'approve') ? 'การร้องขอยานพาหนะได้รับการอนุมัติแล้ว' : 'การร้องขอยานพาหนะถูกปฏิเสธแล้ว'; ?>
				</p>
			</div>
			<?php endif; ?>

			<!-- Car Request Details -->
			<div class="bg-white shadow-md rounded-lg p-8">
				<h2 class="text-3xl font-extrabold text-gray-900 mb-6">รายละเอียด</h2>

				<div class="space-y-6">
					<!-- Core Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลหลัก</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">เรื่อง:</span>
								<?php echo htmlspecialchars($request['title']); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">วัตถุประสงค์:</span>
								<?php echo nl2br(htmlspecialchars($request['purpose'])); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">วันที่ขอใช้:</span>
								<?php echo parseDate($request['request_date']) ?>
							</div>
						</div>
					</div>

					<!-- Car Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลยานพาหนะ</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">รายการ:</span>
								<?php echo ($request['category']) ? $request['category'] : '-' ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ประเภทรถ:</span>
								<?php echo ($request['registration_type']) ? $request['registration_type'] : '-' ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ประเภทการใช้รถ:</span>
								<?php echo ($request['usage_type']) ? $request['usage_type'] : '-' ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ยี้ห้อ:</span>
								<?php echo ($request['make']) ? $request['make'] : '-' ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">รุ่น:</span>
								<?php echo ($request['model']) ? $request['model'] : '-' ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ทะเบียน:</span>
								<?php echo ($request['license_plate']) ? $request['license_plate'] : '-' ?>
							</div>
						</div>
					</div>

					<!-- Personal Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลส่วนตัว</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">ชื่อ</span>
								<?php echo htmlspecialchars($request['first_name']); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">นามสกุล:</span>
								<?php echo htmlspecialchars($request['last_name']); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ตำแหน่ง:</span>
								<?php echo htmlspecialchars($request['position']); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">พร้อมด้วยคณะ: </span>
								<?php echo htmlspecialchars($request['company']); ?>
							</div>
						</div>
					</div>


					<!-- Location Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลสถานที่</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">ปลายทาง:</span>
								<?php echo htmlspecialchars($request['destination'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ตำบล/แขวง:</span>
								<?php echo htmlspecialchars($request['subdistrict'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">อำเภอ/เขต:</span>
								<?php echo htmlspecialchars($request['district'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">จังหวัด:</span>
								<?php echo htmlspecialchars($request['province'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ระหว่างวันที่:</span>
								<?php 
                                    echo ($request['departure_time']) ? parseDate($request['departure_time'], true) : 'N/A'; 
                                ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ถึงวันที่:</span>
								<?php 
                                    echo ($request['return_time']) ? parseDate($request['return_time'],true) : 'N/A'; 
                                ?>
							</div>
						</div>
					</div>

					<!-- Rental Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลเพิ่มเติม</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">จำนวนวัน:</span>
								<?php echo htmlspecialchars($request['manday'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ชื่อผู้ขับ:</span>
								<?php echo htmlspecialchars($request['driver_name'] ?? 'N/A'); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ค่าน้ำมัน:</span>
								<?php echo ($request['oil_expense'] !== null) ?  htmlspecialchars(number_format($request['oil_expense'], 2)) . ' บาท' : 'N/A'; ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">ระยะเดินทางทั้งหมด:</span>
								<?php echo ($request['total_distance'] !== null) ? htmlspecialchars(number_format($request['total_distance'], 2)) . ' กิโล' : 'N/A'; ?>
							</div>
						</div>
					</div>

					<!-- Status Details -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">ข้อมูลสถานะ</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<span class="font-medium text-gray-800">สถานะ:</span>
								<?php
                                    switch (strtolower($request['status_name'])) {
                                        case 'requested':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800'>ส่งคำร้องขอ</span>";
                                            break;
                                        case 'head assigned':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800'>รอหัวหน้างานยานพาหนะอนุมัติ</span>";
                                            break;
                                        case 'supervisor approved':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-sky-100 text-sky-800'>รอหัวหน้ากลุ่มงานบริหารทั่วไปอนุมัติ</span>";
                                            break;
                                        case 'director approved':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800'>รอหัวผู้อำนวยการอนุมัติ</span>";
                                            break;
                                        case 'approved':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>อนุมัติ</span>";
                                            break;
                                        case 'rejected':
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'>ถูกยกเลิก</span>";
                                            break;
                                        default:
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800'>Unknown</span>";
                                    }
                                ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">เวลาส่งคำร้องขอ:</span>
								<?php echo parseDate($request['created_at'],true); ?>
							</div>
							<div>
								<span class="font-medium text-gray-800">แก้ไขล่าสุด:</span>
								<?php echo parseDate($request['updated_at'],true); ?>
							</div>
						</div>
					</div>
					<!-- Remarks -->
					<div>
						<h3 class="text-xl font-semibold text-gray-700 mb-2">หมายเหตุ</h3>
						<p class="text-gray-700">
							<?php echo nl2br(htmlspecialchars($request['remarks'] ?? 'N/A')); ?>
						</p>
					</div>

				</div>

				<!-- Back Button -->
				<div class="mt-6">
					<a href="index.php"
						class="inline-block shrink-0 rounded-md border border-blue-600 bg-blue-600 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-blue-600 focus:outline-none focus:ring active:text-blue-500">
						&larr; กลับสู่หน้าหลัก
					</a>
				</div>
			</div>

			<!-- Approve Button -->
			<?php if (in_array($request['status_name'], ['Supervisor Approved', 'Director Approved'])): ?>
			<div class="flex justify-end mt-5 space-x-4">
				<button id="rejectButton"
					class="inline-block shrink-0 rounded-md border border-red-600 bg-red-600 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-red-600 focus:outline-none focus:ring active:text-red-500">
					ปฏิเสธ
				</button>
				<button id="approveButton"
					class="inline-block shrink-0 rounded-md border border-blue-600 bg-blue-600 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-blue-600 focus:outline-none focus:ring active:text-blue-500">
					อนุมัติ
				</button>
			</div>
			<?php endif; ?>

			<!-- Confirmation Modal -->
			<div id="confirmationModal"
				class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
				<div
					class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
					<div class="px-4 py-5 sm:p-6">
						<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">ยืนยันการอนุมัติ</h3>
						<p class="text-sm text-gray-500">ต้องการอนุมัติการร้องขอยานพาหนะ</p>
					</div>
					<div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
						<form method="POST" class="flex space-x-4">
							<!-- CSRF Token -->
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="action" value="approve">
							<button type="submit"
								class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-3 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
								ยืนยัน
							</button>
							<button type="button" id="cancelButton"
								class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
								ยกเลิก
							</button>
						</form>
					</div>
				</div>
			</div>

			<!-- Rejection Confirmation Modal -->
			<div id="rejectConfirmationModal"
				class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
				<div
					class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
					<div class="px-4 py-5 sm:p-6">
						<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">ยืนยันการปฏิเสธ</h3>
						<p class="text-sm text-gray-500">ต้องการปฏิเสธการร้องขอยานพาหนะหรือไม่?</p>
					</div>
					<div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
						<form method="POST" class="flex space-x-4">
							<!-- CSRF Token -->
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="action" value="reject">
							<button type="submit"
								class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
								ยืนยัน
							</button>
							<button type="button" id="cancelRejectButton"
								class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
								ยกเลิก
							</button>
						</form>
					</div>
				</div>
			</div>



		</div>
	</section>

	<!-- Tailwind JS (for modal functionality if needed) -->
	<script>
	// Get modal elements
	const modal = document.getElementById('confirmationModal');
	const approveButton = document.getElementById('approveButton');
	const cancelButton = document.getElementById('cancelButton');

	const rejectModal = document.getElementById('rejectConfirmationModal');
	const rejectButton = document.getElementById('rejectButton');
	const cancelRejectButton = document.getElementById('cancelRejectButton');

	// Show modal when approve button is clicked
	if (approveButton) {
		approveButton.addEventListener('click', () => {
			modal.classList.remove('hidden');
		});
	}

	// Hide modal when cancel button is clicked
	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			modal.classList.add('hidden');
		});
	}

	// Show modal when reject button is clicked
	if (rejectButton) {
		rejectButton.addEventListener('click', () => {
			rejectModal.classList.remove('hidden');
		});
	}

	// Hide modal when cancel reject button is clicked
	if (cancelRejectButton) {
		cancelRejectButton.addEventListener('click', () => {
			rejectModal.classList.add('hidden');
		});
	}

	// Hide modals when clicking outside the modal content
	window.addEventListener('click', (event) => {
		if (event.target == modal) {
			modal.classList.add('hidden');
		} else if (event.target == rejectModal) {
			rejectModal.classList.add('hidden');
		}
	});
	</script>
</body>

</html>