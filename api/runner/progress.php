<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

try {
    // 1. Récupération de la progression
    $stmt = $pdo->prepare("SELECT current_season_id, current_week_id, current_session_id FROM runner_progress WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $progress = $stmt->fetch();

    if (!$progress) {
        $pdo->prepare("INSERT INTO runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, 1)")->execute([$user['id']]);
        $progress = ["current_season_id" => 1, "current_week_id" => 1, "current_session_id" => 1];
    }

    // 2. Récupération de l'historique complet pour resynchroniser le front-end
    $stmtHistory = $pdo->prepare("SELECT session_id, distance_meters, steps_count FROM session_logs WHERE user_id = ? AND status = 'completed'");
    $stmtHistory->execute([$user['id']]);
    $history = $stmtHistory->fetchAll();

    // 3. Envoi au format exact attendu par le Store
    echo json_encode([
        "status" => "success", 
        "data" => [
            "progress" => $progress,
            "history" => $history
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>