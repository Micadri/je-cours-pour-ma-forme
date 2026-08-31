<?php
require __DIR__ . '/../config.php';
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email et mot de passe requis"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(32));

try {
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, api_token, role) VALUES (?, ?, ?, 'runner')");
    $stmt->execute([$email, $hash, $token]);
    echo json_encode(["status" => "success", "token" => $token]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur lors de la création"]);
}
?>