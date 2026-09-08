<?php
require __DIR__ . '/../config.php';

$user = null;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? null;

if (!$authHeader && isset($_GET['token'])) {
    $authHeader = "Bearer " . $_GET['token'];
}
if ($authHeader) {
    $token = str_replace("Bearer ", "", $authHeader);
    $stmtUser = $pdo->prepare("SELECT id FROM AD_users WHERE api_token = ?");
    $stmtUser->execute([$token]);
    $user = $stmtUser->fetch();
}

$season_id = $_GET['season_id'] ?? null;

try {
    // 1. Détection de la saison active du joueur
    if (!$season_id && $user) {
        $stmtProg = $pdo->prepare("SELECT current_season_id FROM AD_runner_progress WHERE user_id = ?");
        $stmtProg->execute([$user['id']]);
        $prog = $stmtProg->fetch();
        if ($prog && $prog['current_season_id']) {
            $season_id = $prog['current_season_id'];
        }
    }
    
    // Fallback : première saison par défaut
    if (!$season_id) {
        $stmtSeason = $pdo->query("SELECT id FROM AD_seasons ORDER BY order_num ASC LIMIT 1");
        $season_id = $stmtSeason->fetchColumn();
    }

    // 2. Chargement de la saison ciblée
    $stmtSeason = $pdo->prepare("SELECT id, title, order_num FROM AD_seasons WHERE id = ?");
    $stmtSeason->execute([$season_id]);
    $season = $stmtSeason->fetch();

    if (!$season) {
        echo json_encode(["status" => "error", "message" => "Saison introuvable"]);
        exit;
    }

    $stmtWeeks = $pdo->prepare("SELECT id, title, order_num FROM AD_weeks WHERE season_id = ? ORDER BY order_num");
    $stmtWeeks->execute([$season['id']]);
    $weeks = $stmtWeeks->fetchAll();
    
    foreach ($weeks as &$week) {
        $stmtSessions = $pdo->prepare("SELECT id, title, order_num FROM AD_sessions WHERE week_id = ? ORDER BY order_num");
        $stmtSessions->execute([$week['id']]);
        $week['sessions'] = $stmtSessions->fetchAll();
        
        foreach ($week['sessions'] as &$session) {
            $stmtExo = $pdo->prepare("SELECT id, type, duration_seconds, order_num FROM AD_exercises WHERE session_id = ? ORDER BY order_num");
            $stmtExo->execute([$session['id']]);
            $session['exercises'] = $stmtExo->fetchAll();
        }
    }
    $season['weeks'] = $weeks;

    // 3. Chargement du catalogue global
    $stmtAll = $pdo->query("SELECT id, title FROM AD_seasons ORDER BY order_num ASC");
    $allSeasons = $stmtAll->fetchAll();

    echo json_encode(["status" => "success", "data" => $season, "all_seasons" => $allSeasons]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>