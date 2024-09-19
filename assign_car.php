<?php
// assign_car.php

// Start the session to access session variables
session_start();


// Include the database connection file
require_once 'config/connect.php';

// Check if the user is logged in and has the 'Head' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head') {
    header("Location: login.php?error=" . urlencode("Access denied. Please log in as Head to assign cars."));
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

// Initialize variables
$error = '';
$success = '';
$car_assigned = false;

// Handle form submission for Assign, Approve, and Reject
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
    } else {
        // Determine action: assign, approve, or reject
        $action = $_POST['action'] ?? '';

        if ($action === 'assign') {
            $action_performed = 'assign';
            // Assign the selected car
            $selected_car_id = $_POST['car_id'] ?? '';
            if (!filter_var($selected_car_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
                $error = "Please select a valid car.";
            } else {
                try {
                    // Start transaction
                    $conn->beginTransaction();

                    // Check if the selected car is still available
                    $car_check_stmt = $conn->prepare("SELECT status FROM cardetails_view WHERE id = :car_id AND status = 'Available' LIMIT 1");
                    $car_check_stmt->execute(['car_id' => $selected_car_id]);
                    $car_available = $car_check_stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$car_available) {
                        throw new Exception("Selected car is no longer available.");
                    }

                    // Update carrequests table: assign the selected car
                    $update_request_stmt = $conn->prepare("
                        UPDATE carrequests 
                        SET car_id = :car_id, updated_at = CURRENT_TIMESTAMP
                        WHERE id = :request_id
                    ");
                    $update_request_stmt->execute([
                        'car_id' => $selected_car_id,
                        'request_id' => $request_id
                    ]);

                    // Update the car's status to 'In Use'
                    $update_car_stmt = $conn->prepare("UPDATE cars SET status = 'In Use' WHERE id = :car_id");
                    $update_car_stmt->execute(['car_id' => $selected_car_id]);

                    // Commit transaction
                    $conn->commit();
                    $car_assigned = true;
                    $success = "Car assigned successfully.";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollBack();
                    error_log("Error assigning car: " . $e->getMessage());
                    $error = "Error assigning car. Please try again later.";
                }
            }
        } elseif ($action === 'approve') {
            // Approve the car request
            $action_performed = 'approve';
            try {
                $update_status_stmt = $conn->prepare("
                    UPDATE carrequests 
                    SET status_id = (SELECT id FROM requeststatus WHERE name = 'Supervisor Approved' LIMIT 1), updated_at = CURRENT_TIMESTAMP
                    WHERE id = :request_id
                ");
                $update_status_stmt->execute(['request_id' => $request_id]);
                $success = "Request approved successfully.";
				header("Location: index.php?success=" . urlencode("อนุมัติเรียบร้อย"));
				exit();
            } catch (Exception $e) {
                error_log("Error approving request: " . $e->getMessage());
                $error = "Error approving request. Please try again later.";
            }
        } elseif ($action === 'reject') {
            // Reject the car request
            $action_performed = 'reject';
            try {
                $update_status_stmt = $conn->prepare("
                    UPDATE carrequests 
                    SET status_id = (SELECT id FROM requeststatus WHERE name = 'Rejected' LIMIT 1), updated_at = CURRENT_TIMESTAMP
                    WHERE id = :request_id
                ");
                $update_status_stmt->execute(['request_id' => $request_id]);
                $success = "Request rejected successfully.";
				header("Location: index.php?success=" . urlencode("ปฎิเสธเรียบร้อย"));
				exit();
            } catch (Exception $e) {
                error_log("Error rejecting request: " . $e->getMessage());
                $error = "Error rejecting request. Please try again later.";
            }
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
            cr.id = :request_id AND cr.status_id = (
                SELECT id FROM requeststatus WHERE name = 'Head Assigned' LIMIT 1
            )
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
        header("Location: index.php?error=" . urlencode("Car request not found or not in 'Head Assigned' status."));
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error while fetching car request: " . $e->getMessage());
    header("Location: index.php?error=" . urlencode("Database error. Please try again later."));
    exit();
}

// Fetch available cars from cardetails_view
try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            category,
            registration_type,
            usage_type,
            make,
            model,
            year,
            status,
            license_plate
        FROM 
            cardetails_view
        WHERE 
            status = 'Available'
        ORDER BY 
            make ASC, model ASC
    ");
    $stmt->execute();
    $available_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error while fetching available cars: " . $e->getMessage());
    $error = "Database error while fetching available cars. Please try again later.";
}

// Define the stages in order
$stages = ['Requested', 'Head Assigned', 'Supervisor Approved', 'Director Approved', 'Approved'];

// Determine the current stage index based on status_name
$current_stage_index = array_search($request['status_name'], $stages);

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
	<title>จัดสรรยานพาหนะ</title>
	<?php include "includes/font.php"; ?>
	<!-- Tailwind CSS CDN -->
	<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-white">
	<?php include_once "includes/nav.php"; ?>
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
					<?php echo ($action_performed === 'assign') ? 'จัดสรระสำเร็จ' : 'ปฏิเสธสำเร็จ'; ?>
				</h3>
				<p class="text-gray-700">
					<?php echo ($action_performed === 'assign') ? 'จัดสรรยานพาหนะเรียบร้อย' : 'การร้องขอยานพาหนะถูกปฏิเสธแล้ว'; ?>
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
			<?php if (is_null($request['car_id'])): ?>
			<!-- Assign Car Form -->
			<div class="bg-white shadow-md rounded-lg p-8 mb-8 mt-6">
				<h3 class="text-xl font-semibold text-gray-700 mb-6">จัดสรรยานพาหนะ</h3>
				<?php if (count($available_cars) > 0): ?>
				<form action="assign_car.php?id=<?php echo urlencode($request['id']); ?>" method="POST">
					<!-- CSRF Token -->
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

					<div class="mb-6">
						<label for="car_id" class="block text-sm font-medium text-gray-700">ยานพาหนะที่สามารถใช้งาน
							<span class="text-red-500">*</span></label>
						<select id="car_id" name="car_id" required
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
							<option value="">-- เลือกยานพาหนะ --</option>
							<?php foreach ($available_cars as $car): ?>
							<option value="<?php echo htmlspecialchars($car['id']); ?>">
								<?php echo htmlspecialchars("{$car['make']} {$car['model']} ({$car['license_plate']})"); ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>

					<button type="submit" name="action" value="assign"
						class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
						ตกลง
					</button>
				</form>
				<?php else: ?>
				<p>No available cars to assign.</p>
				<?php endif; ?>
			</div>
			<?php endif ?>


			<!-- Approve Button -->
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