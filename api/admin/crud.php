<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Accès refusé"]);
    exit;
}

echo json_encode(["status" => "success", "message" => "Interface CRUD Admin connectée"]);
?>