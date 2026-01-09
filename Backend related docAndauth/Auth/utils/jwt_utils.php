<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

function generateJWT($user_id, $email)
{
  $payload = [
    'iss' => 'localhost',
    'aud' => 'localhost',
    'iat' =>  time(),
    'exp' => time() + (60 * 60),
    'data' => [
      'id' => $user_id,
      'email' => $email
    ]
  ];

  $secret_key = getenv("JWT_SECRET") ?: "defaultSecretKey";
  return JWT::encode($payload, $secret_key, 'HS256');
}

function verifyJWT($token)
{
  $secret_key = getenv("JWT_SECRET") ?: "defaultSecretKey";
  try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    return $decoded;
  } catch (Exception $e) {
    return null;
  }
}
