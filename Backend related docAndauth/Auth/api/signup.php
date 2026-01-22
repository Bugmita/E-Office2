<?php
header("Content-Type: application/json");
include_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$username = $data["username"] ?? '';
$email = $data["email"] ?? '';
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';
$role = $data['role'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
  echo json_encode(["status" => "error", "message" => "All fields are required"]);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => "error", "message" => "Invalid email format"]);
  exit;
}

$allowedDomain = 'nits.ac.in';
$emailDomain = substr(strrchr($email, "@"), 1);


if (!str_ends_with($emailDomain, $allowedDomain)) {
  echo json_encode(["status" => "error", "message" => "Only NIT Silchar institutional emails are allowed"]);
  exit;
}


if ($password !== $confirmPassword) {
  echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
  exit;
}

try {
  // checking email already exist
  $query = "SELECT * FROM users WHERE email = :email";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(":email", $email);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit;
  } else {
    $token = bin2hex(random_bytes(32));

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (username, email, password,role, verification_token, is_verified) VALUES (:username,:email,:password, :role, :token, 0)";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":token", $token);
    $stmt->execute();

    if ($stmt->execute()) {
      // verifying email
      $verifyLink = "http://yourdomain.com/verify.php?token=$token";
      $subject = "Verify your NIT Silchar account";
      $message = "Hi $name,\n\nClick the link below to verify your email:\n$verifyLink\n\nRegards,\nNIT Silchar Portal Team";
      $headers = "From: noreply@nits.ac.in";
      mail($email, $subject, $message, $headers);

      echo json_encode(["status" => "success", "message" => "Registration successful. Check your email to verify your account."]);
    } else {
      echo json_encode(["status" => "error", "message" => "Database error."]);
    }
  }
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage(), 3, "../logs/error.log");
  echo json_encode(["status" => "error", "message" => "Internal server error"]);
}
