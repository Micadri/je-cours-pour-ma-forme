<?php
require '../config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    // Génération d'un token sécurisé
    $token = bin2hex(random_bytes(32));
    
    // Sauvegarde en base
    $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?")->execute([$token, $user['id']]);
    
    echo json_encode(["status" => "success", "token" => $token]);
} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Identifiants incorrects"]);
}
?>