<?php
include_once "../config/db.php";

if (isset($_GET["token"])) {
  $token = $_GET['token'];

  $query = "SELECT * FROM users WHERE verification_token = :token";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(":token", $token);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user["is_verified"] == 1) {

      echo "Account already verified.";
    } else {
      $update = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id";
      $stmt2 = $conn->prepare($update);
      $stmt2->bindParam(":id", $user["id"]);
      $stmt2->execute();
      echo "Email verified successfully! You can now log in.";
    }
  } else {
    echo "Invalid verification link.";
  }
} else {
  echo "No token provided.";
}
