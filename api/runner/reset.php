<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['target_session_id'])) {
    echo json_encode(["status" => "error", "message" => "ID de session manquant"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Mise à jour de la progression
    $stmt = $pdo->prepare("UPDATE runner_progress SET current_session_id = ? WHERE user_id = ?");
    $stmt->execute([$data['target_session_id'], $user['id']]);
    
    // 2. Nettoyage de la DB : On supprime les archives des sessions annulées
    $stmtClean = $pdo->prepare("DELETE FROM session_logs WHERE user_id = ? AND session_id >= ?");
    $stmtClean->execute([$user['id'], $data['target_session_id']]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Progression réinitialisée"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>