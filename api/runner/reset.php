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
    
    // Vérification explicite
    $stmtCheck = $pdo->prepare("SELECT 1 FROM AD_runner_progress WHERE user_id = ?");
    $stmtCheck->execute([$user['id']]);
    
    if (!$stmtCheck->fetch()) {
        $stmtInsert = $pdo->prepare("INSERT INTO AD_runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, ?)");
        $stmtInsert->execute([$user['id'], $data['target_session_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE AD_runner_progress SET current_session_id = ? WHERE user_id = ?");
        $stmt->execute([$data['target_session_id'], $user['id']]);
    }
    
    $stmtClean = $pdo->prepare("DELETE FROM AD_session_logs WHERE user_id = ? AND session_id >= ?");
    $stmtClean->execute([$user['id'], $data['target_session_id']]);
    
    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Progression réinitialisée"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>