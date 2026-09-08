<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);
$data = json_decode(file_get_contents("php://input"), true);
$season_id = $data['season_id'] ?? null;

if (!$season_id) {
    echo json_encode(["status" => "error", "message" => "ID manquant"]);
    exit;
}

try {
    // Trouver le tout premier entraînement de la nouvelle saison
    $stmt = $pdo->prepare("
        SELECT s.id as session_id, w.id as week_id 
        FROM AD_sessions s
        JOIN AD_weeks w ON s.week_id = w.id
        WHERE w.season_id = ? 
        ORDER BY w.order_num ASC, s.order_num ASC LIMIT 1
    ");
    $stmt->execute([$season_id]);
    $first = $stmt->fetch();

    if ($first) {
        // Aligner le marqueur de progression du coureur
        $stmtUpd = $pdo->prepare("UPDATE AD_runner_progress SET current_season_id = ?, current_week_id = ?, current_session_id = ? WHERE user_id = ?");
        $stmtUpd->execute([$season_id, $first['week_id'], $first['session_id'], $user['id']]);
    }
    echo json_encode(["status" => "success", "message" => "Saison modifiée"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>