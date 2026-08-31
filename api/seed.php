<?php
// Configuration PDO pour MAMP
$host = 'localhost'; // ou '127.0.0.1'
$db   = 'JeCoursPourMaForme';
$user = 'root';
$pass = 'root'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 1. Lire le fichier JSON
$json = file_get_contents(__DIR__ . '/data/program_5k.json');
$programData = json_decode($json, true);

if (!$programData) {
    die("Erreur : Impossible de lire ou décoder le fichier JSON.");
}

echo "Début de l'importation...<br>";

// 2. Insérer la Saison
foreach ($programData as $seasonIndex => $season) {
    $stmt = $pdo->prepare("INSERT INTO seasons (title, order_num) VALUES (?, ?)");
    $stmt->execute([$season['label'], $seasonIndex + 1]);
    $seasonId = $pdo->lastInsertId();
    echo "Saison '{$season['label']}' insérée.<br>";

    $currentWeekNum = 0;
    $weekId = null;
    $sessionOrder = 1;

    // 3. Boucler sur les étapes (Sessions)
    foreach ($season['etapes'] as $etape) {
        // Extraction du numéro de semaine depuis le label (ex: "Semaine 1 - Jour 1")
        preg_match('/Semaine (\d+)/', $etape['label'], $matches);
        $weekNum = isset($matches[1]) ? (int)$matches[1] : 1;

        // Si on change de semaine, on crée une nouvelle ligne dans 'weeks'
        if ($weekNum !== $currentWeekNum) {
            $stmt = $pdo->prepare("INSERT INTO weeks (season_id, title, order_num) VALUES (?, ?, ?)");
            $stmt->execute([$seasonId, "Semaine $weekNum", $weekNum]);
            $weekId = $pdo->lastInsertId();
            $currentWeekNum = $weekNum;
            $sessionOrder = 1; // Reset de l'ordre des sessions pour la nouvelle semaine
        }

        // Insérer la session
        $stmt = $pdo->prepare("INSERT INTO sessions (week_id, title, order_num) VALUES (?, ?, ?)");
        $stmt->execute([$weekId, $etape['label'], $sessionOrder]);
        $sessionId = $pdo->lastInsertId();
        $sessionOrder++;

        // 4. Boucler sur les exercices (Steps)
        $exerciseOrder = 1;
        foreach ($etape['steps'] as $step) {
            // Conversion stricte du temps en SECONDES
            $durationSeconds = (int)($step['time'] * 60);

            $stmt = $pdo->prepare("INSERT INTO exercises (session_id, type, duration_seconds, order_num) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sessionId, $step['type'], $durationSeconds, $exerciseOrder]);
            $exerciseOrder++;
        }
    }
}

echo "<br><strong>Importation terminée avec succès !</strong> Ton programme est prêt en base de données.";
?>