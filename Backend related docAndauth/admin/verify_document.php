<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin_login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php';

$status = $_POST['status'];
$remark = trim($_POST['remark']);
$doc_id = filter_input(INPUT_POST, 'doc_id', FILTER_VALIDATE_INT);
if (!$doc_id) {
  die("Invalid document ID");
}

$stmt = $conn->prepare(
  "UPDATE documents
  SET status = :status,
    admin_remark = :remark
  WHERE id = :id"
);


$allowed = ['approved', 'rejected', 'pending'];
if (!in_array($status, $allowed)) {
  die("Invalid status");
}

$stmt->execute([
  'status' => $status,
  'remark' => $remark,
  'id' => $doc_id
]);

if ($stmt->rowCount() === 0) {
  die("No document updated");
}

header("Location: admin_dashboard.php");
exit;
