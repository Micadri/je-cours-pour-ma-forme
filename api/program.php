<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$db   = 'JeCoursPourMaForme';
$user = 'root';
$pass = 'root'; // Laisse vide '' si tu es sur Windows sans mot de passe
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de base de données"]);
    exit;
}

try {
    // 1. Récupérer la saison cible (ici la première pour le MVP)
    $stmtSeason = $pdo->query("SELECT id, title, order_num FROM seasons LIMIT 1");
    $season = $stmtSeason->fetch();

    if (!$season) {
        echo json_encode(["status" => "error", "message" => "Aucune saison trouvée"]);
        exit;
    }

    // 2. Récupérer les semaines de cette saison
    $stmtWeeks = $pdo->prepare("SELECT id, title, order_num FROM weeks WHERE season_id = ? ORDER BY order_num");
    $stmtWeeks->execute([$season['id']]);
    $weeks = $stmtWeeks->fetchAll();

    // 3. Boucler sur les semaines pour y attacher les sessions et exercices
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

    // Attacher l'arbre complet à la saison
    $season['weeks'] = $weeks;

    // 4. Renvoi final
    echo json_encode(["status" => "success", "data" => $season]);

} catch (\Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>