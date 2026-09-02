<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

try {
    $stmt = $pdo->prepare("SELECT current_season_id, current_week_id, current_session_id FROM AD_runner_progress WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $progress = $stmt->fetch();

    if (!$progress) {
        $pdo->prepare("INSERT INTO AD_runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, 1)")->execute([$user['id']]);
        $progress = ["current_season_id" => 1, "current_week_id" => 1, "current_session_id" => 1];
    }

    $stmtHistory = $pdo->prepare("SELECT session_id, distance_meters, steps_count FROM AD_session_logs WHERE user_id = ? AND status = 'completed'");
    $stmtHistory->execute([$user['id']]);
    $history = $stmtHistory->fetchAll();

    $stmtProfile = $pdo->prepare("SELECT first_name, theme, audio_enabled, avatar FROM AD_users WHERE id = ?");
    $stmtProfile->execute([$user['id']]);
    $profile = $stmtProfile->fetch();

    echo json_encode([
        "status" => "success", 
        "data" => [
            "progress" => $progress,
            "history" => $history,
            "profile" => $profile
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>