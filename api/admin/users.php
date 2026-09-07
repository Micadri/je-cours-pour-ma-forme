<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Accès refusé"]);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    // Récupère les coureurs avec leur progression et statistiques globales
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
    $runners = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $runners]);
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
        // En-têtes des colonnes CSV
        fputcsv($output, ['ID', 'Prenom', 'Email', 'Date_Inscription', 'Sessions_Terminees']);
        foreach ($runners as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }
}
?>