<?php 
	$host = "localhost";
	$db_username = "admin";
	$db_password = "adm1n";
	$dbname = "carrenting";
	
	try {
		$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		die("Database connection failed: " . $e->getMessage());
	}
?>