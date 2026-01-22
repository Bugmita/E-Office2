<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: /Auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php'; // adjust path if needed

$uid  = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'all';

try {
  if ($type === 'all') {
    $stmt = $conn->prepare(
      "SELECT * FROM documents WHERE user_id = :uid"
    );
    $stmt->execute(['uid' => $uid]);
  } else {
    $stmt = $conn->prepare(
      "SELECT * FROM documents 
             WHERE user_id = :uid AND file_type = :type"
    );
    $stmt->execute([
      'uid'  => $uid,
      'type' => $type
    ]);
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rows as $row) {
    echo "
        <tr>
          <td>{$row['name']}</td>
          <td>{$row['file_type']}</td>
          <td>{$row['uploaded_at']}</td>
          <td>{$row['status']}</td>
          <td>
            <a href='view.php?id={$row['id']}'>View</a>
            <a href='download.php?id={$row['id']}'>Download</a>
            <a href='delete.php?id={$row['id']}'>Delete</a>
          </td>
        </tr>";
  }
} catch (PDOException $e) {
  die("Failed to fetch documents");
}
