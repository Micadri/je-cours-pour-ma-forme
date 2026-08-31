<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo); // Le script s'arrête ici si le token est faux

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['target_session_id'])) {
    echo json_encode(["status" => "error", "message" => "ID de session manquant"]);
    exit;
}

try {
    // Mise à jour de la progression du coureur connecté
    $stmt = $pdo->prepare("UPDATE runner_progress SET current_session_id = ? WHERE user_id = ?");
    $stmt->execute([$data['target_session_id'], $user['id']]);
    
    echo json_encode(["status" => "success", "message" => "Progression réinitialisée"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>