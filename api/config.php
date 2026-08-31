<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

try {
    $pdo = new PDO("mysql:host=localhost;dbname=JeCoursPourMaForme;charset=utf8mb4", "root", "root", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connexion BDD échouée"]);
    exit;
}

// Fonction de vérification du Token
function requireAuth($pdo) {
    $authHeader = null;
    
    if (isset($_SERVER['Authorization'])) {
        $authHeader = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $authHeader = trim($requestHeaders['Authorization']);
        }
    }
    
    // LE PLAN B POUR MAMP : Récupération via l'URL si l'en-tête est bloqué
    if (!$authHeader && isset($_GET['token'])) {
        $authHeader = "Bearer " . trim($_GET['token']);
    }

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token manquant"]);
        exit;
    }

    // Nettoyage de la chaîne "Bearer "
    $token = str_replace("Bearer ", "", $authHeader);
    
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE api_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token invalide"]);
        exit;
    }
    return $user;
}
?>