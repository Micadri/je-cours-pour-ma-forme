<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

try {
    $stmt = $pdo->prepare("SELECT current_season_id, current_week_id, current_session_id FROM runner_progress WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $progress = $stmt->fetch();

    if (!$progress) {
        // Initialisation à la session 1
        $pdo->prepare("INSERT INTO runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, 1)")->execute([$user['id']]);
        $progress = ["current_season_id" => 1, "current_week_id" => 1, "current_session_id" => 1];
    }

    echo json_encode(["status" => "success", "data" => $progress]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>