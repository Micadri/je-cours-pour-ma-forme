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

    $stmtLog = $pdo->prepare("
        INSERT INTO AD_session_logs 
        (user_id, session_id, status, distance_meters, steps_count, actual_duration_seconds, elevation_gain, route_data, weather_temp) 
        VALUES (?, ?, 'completed', ?, ?, ?, ?, ?, ?)
    ");
    
    $distance = $data['distance_meters'] ?? 0;
    $steps = $data['steps_count'] ?? 0;
    $duration = $data['actual_duration_seconds'] ?? 0;
    $elevation = $data['elevation_gain'] ?? 0;
    $route = $data['route_data'] ?? null;
    $weather = $data['weather_temp'] ?? null;
    
    $stmtLog->execute([
        $user['id'], $data['session_id'], $distance, $steps, 
        $duration, $elevation, $route, $weather
    ]);

    $stmtCheck = $pdo->prepare("SELECT 1 FROM AD_runner_progress WHERE user_id = ?");
    $stmtCheck->execute([$user['id']]);

    if (!$stmtCheck->fetch()) {
        $stmtInsert = $pdo->prepare("INSERT INTO AD_runner_progress (user_id, current_season_id, current_week_id, current_session_id) VALUES (?, 1, 1, ?)");
        $stmtInsert->execute([$user['id'], $data['next_session_id']]);
    } else {
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