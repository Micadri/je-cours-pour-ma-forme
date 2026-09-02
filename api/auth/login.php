<?php
require '../config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 10; // Minutes

// Vérification des tentatives
$stmt = $pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
$stmt->execute([$ip]);
$attempt_data = $stmt->fetch();

if ($attempt_data && $attempt_data['attempts'] >= $max_attempts) {
    $last_attempt = strtotime($attempt_data['last_attempt']);
    if (time() - $last_attempt < ($lockout_time * 60)) {
        http_response_code(429);
        echo json_encode(["status" => "error", "message" => "Trop de tentatives. Réessayez dans 10 minutes."]);
        exit;
    } else {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
    }
}

$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]); // Reset au succès
    $token = bin2hex(random_bytes(32));
    $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?")->execute([$token, $user['id']]);
    echo json_encode(["status" => "success", "token" => $token]);
} else {
    // Incrémentation des échecs
    $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1")->execute([$ip]);
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Identifiants incorrects"]);
}
?>