<?php
require __DIR__ . '/../config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$first_name = trim($data['first_name'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'];

// Sécurité : 3 tentatives par heure maximum
$max_attempts = 3;
$lockout_time = 60; // Minutes

// Vérification de la limitation IP
$stmt = $pdo->prepare("SELECT attempts, last_attempt FROM register_attempts WHERE ip_address = ?");
$stmt->execute([$ip]);
$attempt_data = $stmt->fetch();

if ($attempt_data && $attempt_data['attempts'] >= $max_attempts) {
    $last_attempt = strtotime($attempt_data['last_attempt']);
    if (time() - $last_attempt < ($lockout_time * 60)) {
        http_response_code(429);
        echo json_encode(["status" => "error", "message" => "Trop de tentatives. Veuillez patienter une heure."]);
        exit;
    } else {
        // Le délai est passé, on purge l'historique
        $pdo->prepare("DELETE FROM register_attempts WHERE ip_address = ?")->execute([$ip]);
    }
}

// Fonction utilitaire pour enregistrer un échec et bloquer
function logAttemptAndExit($pdo, $ip, $message) {
    $pdo->prepare("INSERT INTO register_attempts (ip_address, attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1")->execute([$ip]);
    echo json_encode(["status" => "error", "message" => $message]);
    exit;
}

if (!$email || !$password || !$first_name) {
    logAttemptAndExit($pdo, $ip, "Prénom, email et mot de passe requis");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logAttemptAndExit($pdo, $ip, "Format d'adresse email invalide");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    logAttemptAndExit($pdo, $ip, "Cet email est déjà utilisé");
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(32));

try {
    $stmt = $pdo->prepare("INSERT INTO users (email, first_name, password_hash, api_token, role) VALUES (?, ?, ?, ?, 'runner')");
    $stmt->execute([$email, $first_name, $hash, $token]);
    
    // Succès : on supprime l'historique d'échecs éventuel pour cette IP
    $pdo->prepare("DELETE FROM register_attempts WHERE ip_address = ?")->execute([$ip]);
    
    echo json_encode(["status" => "success", "token" => $token]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur lors de la création"]);
}
?>