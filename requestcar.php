<?php
// requestcar.php

// Start session
session_start();

// Include the navigation bar

// Include the database connection
require_once 'config/connect.php';

// Check if the user is logged in and has the Staff role
if (!isset($_SESSION['user_id']) ) {
    header("Location: login.php?error=" . urlencode("Access denied. Please log in as Staff to create a car request."));
    exit();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create Car Rental Request - Car Rental</title>
	<!-- Tailwind CSS CDN -->
	<?php include "includes/font.php"; ?>
	<!-- Flatpickr CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body>
	<?php include "includes/nav.php"; ?>
	<section class="py-12 bg-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<h2 class="text-3xl font-extrabold text-gray-900 mb-6">ร้องขอใช้ยานพาหนะ</h2>

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

			<!-- Car Request Form -->
			<div class="bg-white shadow-md rounded-lg p-8">
				<form action="handles/requestcar.php" method="POST">
					<!-- CSRF Token -->
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

					<!-- Core Details -->
					<div class="mb-6">
						<h3 class="text-xl font-semibold text-gray-700 mb-4">ข้อมูลหลัก</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<!-- Title -->
							<div>
								<label for="title" class="block text-sm font-medium text-gray-700">เรื่อง <span
										class="text-red-500">*</span></label>
								<input type="text" id="title" name="title" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="ขออนุญาตใช้รถยนต์ไปราชการ">
							</div>

							<!-- Request Date -->
							<div>
								<label for="request_date" class="block text-sm font-medium text-gray-700">วันที่ร้องขอ
									<span class="text-red-500">*</span></label>
								<input type="date" id="request_date" name="request_date" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									value="<?php echo date('Y-m-d'); ?>">
							</div>


						</div>
						<!-- Purpose -->
						<div class="mb-6 mt-4">
							<label for="purpose" class="block text-sm font-medium text-gray-700">วัตถุประสงค์ <span
									class="text-red-500">*</span></label>
							<textarea id="purpose" name="purpose" required
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
								rows="3" placeholder="อธิบายวัตถุประสงค์การใช้งานยานพาหนะ"></textarea>
						</div>
					</div>

					<!-- Personal Details -->
					<div class="mb-6">
						<h3 class="text-xl font-semibold text-gray-700 mb-4">ข้อมูลส่วนตัว</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<!-- First Name -->
							<div>
								<label for="first_name" class="block text-sm font-medium text-gray-700">ชื่อ <span
										class="text-red-500">*</span></label>
								<input type="text" id="first_name" name="first_name" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="อาดัม">
							</div>

							<!-- Last Name -->
							<div>
								<label for="last_name" class="block text-sm font-medium text-gray-700">นามสกุล <span
										class="text-red-500">*</span></label>
								<input type="text" id="last_name" name="last_name" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="อิดรีส">
							</div>

							<!-- Position -->
							<div class="">
								<label for="position" class="block text-sm font-medium text-gray-700">ตำแหน่ง <span
										class="text-red-500">*</span></label>
								<input type="text" id="position" name="position" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="พยาบาล">
							</div>

							<!-- with -->
							<div class="">
								<label for="company" class="block text-sm font-medium text-gray-700">พร้อมด้วยคณะ <span
										class="text-red-500">*</span></label>
								<input type="text" id="company" name="company" required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="คณะกรรมการจำนวน 5 คน">
							</div>
						</div>
					</div>


					<!-- Location Details -->
					<div class="mb-6">
						<h3 class="text-xl font-semibold text-gray-700 mb-4">ข้อมูลสถานที่</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<!-- Destination -->
							<div>
								<label for="destination" class="block text-sm font-medium text-gray-700">ปลายทาง</label>
								<input type="text" id="destination" name="destination"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="ที่ว่าการอำเภอจังหวัดปัตตานี">
							</div>

							<!-- Subdistrict -->
							<div>
								<label for="subdistrict"
									class="block text-sm font-medium text-gray-700">ตำบล/แขวง</label>
								<input type="text" id="subdistrict" name="subdistrict"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="รูสะมิแล">
							</div>

							<!-- District -->
							<div>
								<label for="district" class="block text-sm font-medium text-gray-700">อำเภอ/เขต</label>
								<input type="text" id="district" name="district"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="เมือง">
							</div>

							<!-- Province -->
							<div>
								<label for="province" class="block text-sm font-medium text-gray-700">จังหวัด</label>
								<input type="text" id="province" name="province"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="ปัตตานี">
							</div>

							<!-- Departure Time -->
							<div>
								<label for="departure_time"
									class="block text-sm font-medium text-gray-700">ระหว่างวันที่</label>
								<input type="text" id="departure_time" name="departure_time" required
									placeholder="เลือกวันที่และเวลา"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
							</div>

							<!-- Return Time -->
							<div>
								<label for="return_time"
									class="block text-sm font-medium text-gray-700">ถึงวันที่</label>
								<input type="text" id="return_time" name="return_time" lang="en-GB"
									placeholder="เลือกวันที่และเวลา"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
							</div>
						</div>
					</div>

					<!-- Rental Details -->
					<div class="mb-6">
						<h3 class="text-xl font-semibold text-gray-700 mb-4">ข้อมูลเพิ่มเติม</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<!-- Manday -->
							<div>
								<label for="manday" class="block text-sm font-medium text-gray-700">จำนวนวัน</label>
								<input type="number" id="manday" name="manday" min="0"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="2">
							</div>

							<!-- Driver Name -->
							<div>
								<label for="driver_name"
									class="block text-sm font-medium text-gray-700">ชื่อผู้ขับ</label>
								<input type="text" id="driver_name" name="driver_name"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="อาดัม">
							</div>

							<!-- Oil Expense -->
							<div>
								<label for="oil_expense"
									class="block text-sm font-medium text-gray-700">ค่าน้ำมัน</label>
								<input type="number" id="oil_expense" name="oil_expense" step="0.01" min="0"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="500">
							</div>

							<!-- Total Distance -->
							<div>
								<label for="total_distance"
									class="block text-sm font-medium text-gray-700">ระยะเดินทางทั้งหมด
									(กิโล)</label>
								<input type="number" step="0.01" id="total_distance" name="total_distance" min="0"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									placeholder="275">
							</div>
						</div>
					</div>


					<!-- Return Time and Remarks -->
					<div class="mb-6">
						<div class="grid grid-cols-1 md:grid-cols-1 gap-4">

							<!-- Remarks -->
							<div>
								<label for="remarks" class="block text-sm font-medium text-gray-700">หมายเหตุ</label>
								<textarea id="remarks" name="remarks"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
									rows="3" placeholder="หมายเหตุเพิ่มเติม"></textarea>
							</div>
						</div>
					</div>

					<!-- Submit Button -->
					<div>
						<button type="submit"
							class="inline-block shrink-0 rounded-md border border-blue-600 bg-blue-600 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-blue-600 focus:outline-none focus:ring active:text-blue-500 w-full">
							ส่งคำร้องขอ
						</button>
					</div>
				</form>
			</div>
		</div>
	</section>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
	<script>
	flatpickr("#departure_time", {
		locale: "th",
		enableTime: true,
		dateFormat: "Y-m-d H:i:S",
		time_24hr: true
	});
	flatpickr("#return_time", {
		locale: "th",
		enableTime: true,
		dateFormat: "Y-m-d H:i:S",
		time_24hr: true
	});
	</script>
</body>

</html>