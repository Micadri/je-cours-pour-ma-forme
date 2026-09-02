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
    $stmtLog = $pdo->prepare("INSERT INTO AD_session_logs (user_id, session_id, status, distance_meters, steps_count) VALUES (?, ?, 'completed', ?, ?)");
    $distance = $data['distance_meters'] ?? 0;
    $steps = $data['steps_count'] ?? 0;
    
    $stmtLog->execute([$user['id'], $data['session_id'], $distance, $steps]);
    $stmtProgress = $pdo->prepare("UPDATE AD_runner_progress SET current_session_id = ? WHERE user_id = ?");
    $stmtProgress->execute([$data['next_session_id'], $user['id']]);
    
    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Course validée"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>