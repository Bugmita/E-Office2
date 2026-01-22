<?php
// Authorization
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization");

include_once "../utils/jwt_utils.php";

$headers = getallheaders();
if (isset($headers['Authorization'])) {
  $token = trim(str_replace('Bearer', '', $headers['Authorization']));
  $decoded = verifyJWT($token);

  if ($decoded) {
    echo json_encode([
      "status" => "success",
      "message" => "Access granted",
      "user" => $decoded->data
    ]);
  } else {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Authorization header missing"]);
}
