<?php
// api/config.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

try {
    // Si tu es sur Windows sans mot de passe, remplace "root", "root" par "root", ""
    $pdo = new PDO("mysql:host=localhost;dbname=JeCoursPourMaForme;charset=utf8mb4", "root", "root", [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connexion échouée"]);
    exit;
}
?>