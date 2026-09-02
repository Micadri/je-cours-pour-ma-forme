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

    // 1. Ajout de steps_count dans la requête SQL
    $stmtLog = $pdo->prepare("INSERT INTO session_logs (user_id, session_id, status, distance_meters, steps_count) VALUES (?, ?, 'completed', ?, ?)");
    
    // 2. Récupération des nouvelles variables exactes envoyées par le front-end
    $distance = $data['distance_meters'] ?? 0;
    $steps = $data['steps_count'] ?? 0;

    // 3. Exécution avec les 4 paramètres
    $stmtLog->execute([$user['id'], $data['session_id'], $distance, $steps]);

    $stmtProgress = $pdo->prepare("UPDATE runner_progress SET current_session_id = ? WHERE user_id = ?");
    $stmtProgress->execute([$data['next_session_id'], $user['id']]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Course validée"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>