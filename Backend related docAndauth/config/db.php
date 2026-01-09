<?php
$host = "localhost";
$port = "3306";
$db_name = "auth_system";
$username = "root";
$password = "Alokesh@1";

try {
  $conn = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
  exit;
}
