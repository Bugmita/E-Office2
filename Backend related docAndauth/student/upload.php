<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: /Auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php';

$name = $_POST['name'] ?? '';
$type = $_POST['type'] ?? '';
$file = $_FILES['file'] ?? null;

if (empty($name) || empty($type) || !$file) {
  die("Invalid data");
}

/* ---------- FILE UPLOAD ---------- */
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = time() . "_" . uniqid() . "." . $ext;

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

/* ---------- DB INSERT (PDO) ---------- */
try {
  $stmt = $conn->prepare(
    "INSERT INTO documents (user_id, name, file_name, file_type)
         VALUES (:uid, :name, :file, :type)"
  );

  $stmt->execute([
    'uid'  => $_SESSION['user_id'],
    'name' => $name,
    'file' => $filename,
    'type' => $type
  ]);

  header("Location: documents.php");
  exit;
} catch (PDOException $e) {
  die("Upload failed");
}
