<?php
// index.php

// Start session
session_start();

// Include the navigation bar

// Include the database connection
require_once 'config/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=" . urlencode("Please log in to view your car requests."));
    exit();
}

// Determine user role
$user_role = $_SESSION['role'] ?? 'Staff'; // Default to 'Staff' if role not set

// Prepare SQL query based on user role
if ($user_role === 'Staff') {
    // Staff: Query only the user's own car requests
    $sql = "
        SELECT 
            cr.id,
            cr.title,
            cr.purpose,
            cr.request_date,
            cr.destination,
            rs.name AS status_name
        FROM 
            carrequests cr
        INNER JOIN 
            requeststatus rs ON cr.status_id = rs.id
        WHERE 
            cr.user_id = :user_id
        ORDER BY 
            cr.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
}  else {
    // Head: Query all car requests with status 'Head Assigned'
    $sql = "
        SELECT 
            cr.id,
            cr.title,
            cr.purpose,
            cr.request_date,
            cr.destination,
            rs.name AS status_name
        FROM 
            carrequests cr
        INNER JOIN 
            requeststatus rs ON cr.status_id = rs.id
        ORDER BY 
            cr.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$car_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>My Car Requests - Car Rental</title>
	<?php include "includes/font.php"; ?>
	</style>
	<!-- Tailwind CSS CDN -->
	<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-gray-100">
	<?php include "includes/nav.php"; ?>
	<section class="py-12">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<h2 class="text-3xl font-bold text-gray-900 mb-6">รายการขอใช้ยานพาหนะของฉัน</h2>

			<!-- Display Success or Error Messages -->
			<?php if (isset($_GET['success'])): ?>
			<div class="mb-4 p-4 text-green-700 bg-green-100 rounded">
				<?php echo htmlspecialchars($_GET['success']); ?>
			</div>
			<?php endif; ?>

			<?php if (isset($_GET['error'])): ?>
			<div class="mb-4 p-4 text-red-700 bg-red-100 rounded">
				<?php echo htmlspecialchars($_GET['error']); ?>
			</div>
			<?php endif; ?>

			<!-- Button to Create a New Car Request (Visible to Staff) -->
			<?php if ($user_role === 'Staff'): ?>
			<div class="mb-6">
				<a href="requestcar.php"
					class="inline-block shrink-0 rounded-md border border-blue-600 bg-blue-600 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-blue-600 focus:outline-none focus:ring active:text-blue-500">
					ร้องขอยานพาหนะ
				</a>
			</div>
			<?php endif; ?>

			<!-- Display Car Requests -->
			<?php if (count($car_requests) > 0): ?>
			<div class="overflow-x-auto">
				<table class="min-w-full bg-white shadow-md rounded-lg">
					<thead>
						<tr>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								เรื่อง</th>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								วัตถุประสงค์</th>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								วันที่ขอใช้</th>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								ปลายทาง</th>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								สถานะ</th>
							<th
								class="py-3 px-6 bg-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
								Actions
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($car_requests as $request): ?>
						<tr class="border-t">
							<td class="py-4 px-6 text-sm text-gray-800">
								<?php echo htmlspecialchars($request['title']); ?></td>
							<td class="py-4 px-6 text-sm text-gray-800">
								<?php echo htmlspecialchars($request['purpose']); ?></td>
							<td class="py-4 px-6 text-sm text-gray-800">
								<?php 
                                 // Array of Thai month names
                                $thai_months = [
                                    1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                                ];

                                // Create a DateTime object
                                $date = new DateTime($request['request_date']);

                                // Extract day, month, and year
                                $day = $date->format('j');
                                $month = (int)$date->format('n'); // Month as number without leading zeros
                                $year = $date->format('Y');

                                // Adjust year for Buddhist Era if needed
                                $year_be = $year + 543;

                                // Format the date
                                $formatted_date = "{$day} {$thai_months[$month]} {$year_be}";

                                echo htmlspecialchars($formatted_date);
                                ?>
							</td>
							<td class="py-4 px-6 text-sm text-gray-800">
								<?php 
                                echo htmlspecialchars($request['destination'] ?? 'N/A'); 
                                ?></td>
							<td class="py-4 px-6 text-sm text-gray-800">
								<?php
                                            switch (strtolower($request['status_name'])) {
                                                case 'requested':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800'>ร้องขอเรียบร้อย</span>";
                                                    break;
                                                case 'head assigned':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800'>รอหัวหน้างานยานพาหนะอนุมัติ</span>";
                                                    break;
                                                case 'supervisor approved':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-sky-100 text-sky-800'>รอหัวหน้ากลุ่มงานบริหารทั่วไปอนุมัติ</span>";
                                                    break;
                                                case 'director approved':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800'>รอผู้อำนวนการอนุมัติ</span>";
                                                    break;
                                                case 'approved':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>อนุมัติ</span>";
                                                    break;
                                                case 'cancelled':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800'>ยกเลิก</span>";
                                                    break;
                                                case 'rejected':
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'>ถูกยกเลิก</span>";
                                                    break;
                                                default:
                                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800'>ไม่ทราบ</span>";
                                            }
                                        ?>
							</td>
							<?php if ($user_role === 'Staff'): ?>
							<td class="py-4 px-6 text-sm">
								<a href="view_request.php?id=<?php echo urlencode($request['id']); ?>"
									class="text-blue-600 hover:text-blue-900 mr-4">รายละเอียด</a>
							</td>
							<?php elseif ($user_role === 'Head' && $request['status_name'] === 'Head Assigned'): ?>
							<td class="py-4 px-6 text-sm">
								<a href="assign_car.php?id=<?php echo urlencode($request['id']); ?>"
									class="text-green-600 hover:text-green-900">จัดสรรยานพาหนะ</a>
							</td>
							<?php elseif ($user_role === 'Supervisor' && $request['status_name'] === 'Supervisor Approved'): ?>
							<td class="py-4 px-6 text-sm">
								<a href="approve.php?id=<?php echo urlencode($request['id']); ?>"
									class="text-green-600 hover:text-green-900">อนุมัติ</a>
							</td>
							<?php elseif ($user_role === 'Director' && $request['status_name'] === 'Director Approved'):?>
							<td class="py-4 px-6 text-sm">
								<a href="approve.php?id=<?php echo urlencode($request['id']); ?>"
									class="text-green-600 hover:text-green-900">อนุมัติ</a>
							</td>
							<?php endif; ?>

							<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<p class="text-gray-700">No car rental requests found.</p>
			<?php endif; ?>
		</div>
	</section>
</body>

</html>