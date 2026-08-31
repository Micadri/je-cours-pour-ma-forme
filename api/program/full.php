<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

try {
    $stmtSeason = $pdo->query("SELECT id, title, order_num FROM seasons LIMIT 1");
    $season = $stmtSeason->fetch();

    $stmtWeeks = $pdo->prepare("SELECT id, title, order_num FROM weeks WHERE season_id = ? ORDER BY order_num");
    $stmtWeeks->execute([$season['id']]);
    $weeks = $stmtWeeks->fetchAll();

    foreach ($weeks as &$week) {
        $stmtSessions = $pdo->prepare("SELECT id, title, order_num FROM sessions WHERE week_id = ? ORDER BY order_num");
        $stmtSessions->execute([$week['id']]);
        $week['sessions'] = $stmtSessions->fetchAll();

        foreach ($week['sessions'] as &$session) {
            $stmtExo = $pdo->prepare("SELECT id, type, duration_seconds, order_num FROM exercises WHERE session_id = ? ORDER BY order_num");
            $stmtExo->execute([$session['id']]);
            $session['exercises'] = $stmtExo->fetchAll();
        }
    }
    $season['weeks'] = $weeks;
    echo json_encode(["status" => "success", "data" => $season]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>