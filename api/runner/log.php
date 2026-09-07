<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['session_id'], $data['next_session_id'])) {
    echo json_encode(["status" => "error", "message" => "Données manquantes"]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 1. Ajouter l'historique de la course
    $stmtLog = $pdo->prepare("INSERT INTO AD_session_logs (user_id, session_id, status, distance_meters, steps_count) VALUES (?, ?, 'completed', ?, ?)");
    $distance = $data['distance_meters'] ?? 0;
    $steps = $data['steps_count'] ?? 0;
    $stmtLog->execute([$user['id'], $data['session_id'], $distance, $steps]);
    
    // 2. Vérification explicite de la progression
    $stmtCheck = $pdo->prepare("SELECT 1 FROM AD_runner_progress WHERE user_id = ?");
    $stmtCheck->execute([$user['id']]);
    
    if (!$stmtCheck->fetch()) {
        // Nouvel inscrit : on initialise sa ligne de progression
        $stmtInsert = $pdo->prepare("INSERT INTO AD_runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, ?)");
        $stmtInsert->execute([$user['id'], $data['next_session_id']]);
    } else {
        // Utilisateur existant : on met à jour sa ligne
        $stmtProgress = $pdo->prepare("UPDATE AD_runner_progress SET current_session_id = ? WHERE user_id = ?");
        $stmtProgress->execute([$data['next_session_id'], $user['id']]);
    }
    
    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Course validée"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>