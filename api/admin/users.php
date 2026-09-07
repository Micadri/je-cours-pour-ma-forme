<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Accès refusé"]);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT u.id, u.first_name, u.email, u.created_at,
                   p.current_season_id, p.current_week_id, p.current_session_id,
                   COUNT(l.id) as total_sessions,
                   SUM(l.distance_meters) as total_distance
            FROM AD_users u
            LEFT JOIN AD_runner_progress p ON u.id = p.user_id
            LEFT JOIN AD_session_logs l ON u.id = l.user_id AND l.status = 'completed'
            WHERE u.role = 'runner'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);

    } elseif ($action === 'history') {
        $user_id = $_GET['user_id'] ?? 0;
        
        // Utilisation de l.* pour éviter de planter si created_at n'existe pas
        // Utilisation de LEFT JOIN pour forcer l'affichage même si un entraînement a été supprimé
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   COALESCE(s.title, 'Entraînement inconnu') as session_title, 
                   COALESCE(w.title, 'Semaine inconnue') as week_title, 
                   COALESCE(sea.title, 'Saison inconnue') as season_title
            FROM AD_session_logs l
            LEFT JOIN AD_sessions s ON l.session_id = s.id
            LEFT JOIN AD_weeks w ON s.week_id = w.id
            LEFT JOIN AD_seasons sea ON w.season_id = sea.id
            WHERE l.user_id = ? AND l.status = 'completed'
            ORDER BY l.id DESC
        ");
        $stmt->execute([$user_id]);
        echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);

    } elseif ($action === 'export') {
        $format = $_GET['format'] ?? 'csv';
        $stmt = $pdo->query("
            SELECT u.id, u.first_name, u.email, u.created_at, 
                   IFNULL(COUNT(l.id), 0) as sessions_terminees
            FROM AD_users u
            LEFT JOIN AD_session_logs l ON u.id = l.user_id AND l.status = 'completed'
            WHERE u.role = 'runner'
            GROUP BY u.id
        ");
        $runners = $stmt->fetchAll();

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="coureurs.json"');
            echo json_encode($runners, JSON_PRETTY_PRINT);
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="coureurs.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Prenom', 'Email', 'Date_Inscription', 'Sessions_Terminees']);
            foreach ($runners as $row) { fputcsv($output, $row); }
            fclose($output);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur SQL : " . $e->getMessage()]);
}
?>