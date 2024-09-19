<?php
// includes/nav.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';
?>
<nav class="bg-blue-600">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex items-center justify-between h-16">
			<!-- Logo Section -->
			<div class="flex-shrink-0">
				<a href="index.php" class="flex items-center">
					<!-- SVG Logo or Image -->
					<svg class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						stroke="currentColor">
						<!-- Example Icon (Replace with your own) -->
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M12 8c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 10c-2.761 0-5-2.239-5-5h2a3 3 0 106 0h2c0 2.761-2.239 5-5 5z" />
					</svg>
					<span class="text-white font-bold text-xl ml-2">ระบบขอใช้ยานพาหนะ</span>
				</a>
			</div>

			<!-- Navigation Links -->
			<div class="hidden md:block">
				<div class="ml-10 flex items-baseline space-x-4">
					<a href="index.php"
						class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">หน้าหลัก</a>
					<?php if ($isLoggedIn): ?>
					<a href="logout.php"
						class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">ออกจากระบบ</a>
					<span class="text-white px-3 py-2 rounded-md text-sm font-medium">สวัสดี,
						<?php echo $username; ?>!</span>
					<?php else: ?>
					<a href="signup.php"
						class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">สมัครสมาชิก</a>
					<a href="login.php"
						class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">เข้าสู่ระบบ</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Mobile Menu Button -->
			<div class="-mr-2 flex md:hidden">
				<button id="mobile-menu-button" type="button"
					class="bg-blue-600 inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white"
					aria-controls="mobile-menu" aria-expanded="false">
					<span class="sr-only">Open main menu</span>
					<!-- Icon when menu is closed. -->
					<svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M4 6h16M4 12h16M4 18h16" />
					</svg>
					<!-- Icon when menu is open. -->
					<svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
		</div>
	</div>

	<!-- Mobile Menu -->
	<div id="mobile-menu" class="hidden md:hidden">
		<div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
			<a href="index.php"
				class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Home</a>
			<?php if ($isLoggedIn): ?>
			<a href="dashboard.php"
				class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Dashboard</a>
			<a href="logout.php"
				class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Log Out</a>
			<span class="text-white block px-3 py-2 rounded-md text-base font-medium">Hello,
				<?php echo $username; ?>!</span>
			<?php else: ?>
			<a href="signup.php"
				class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Sign Up</a>
			<a href="login.php"
				class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Log In</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Mobile Menu Toggle Script -->
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const menuButton = document.getElementById('mobile-menu-button');
		const mobileMenu = document.getElementById('mobile-menu');
		const menuIcons = menuButton.querySelectorAll('svg');

		menuButton.addEventListener('click', function() {
			mobileMenu.classList.toggle('hidden');
			menuIcons[0].classList.toggle('hidden');
			menuIcons[1].classList.toggle('hidden');
		});
	});
	</script>
</nav>