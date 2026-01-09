<?php
session_start();
header("Content-Type: application/json");

include_once "../config/db.php";
include_once "../utils/jwt_utils.php";

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
  $stmt->bindParam(":email", $data->email);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($data->password, $user["password"])) {

      $_SESSION['user_id'] = $user['id'];
      $_SESSION['email']   = $user['email'];

      $token = generateJWT($user['id'], $user['email']);

      echo json_encode([
        "status" => "success",
        "token" => $token
      ]);
      exit;
    }
  }
}

echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
