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
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   COALESCE(s.order_num, 1) as session_index, 
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
        
    } elseif ($action === 'feedbacks') {
        $stmt = $pdo->query("
            SELECT f.*, u.first_name, u.email 
            FROM AD_feedbacks f 
            LEFT JOIN AD_users u ON f.user_id = u.id 
            ORDER BY f.created_at DESC
        ");
        echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
        
    } elseif ($action === 'delete_feedback') {
        $feedback_id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM AD_feedbacks WHERE id = ?");
        $stmt->execute([$feedback_id]);
        echo json_encode(["status" => "success"]);

        } elseif ($action === 'accept_feedback') {
        // NOUVELLE ROUTE : Marquer comme traité
        $feedback_id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("UPDATE AD_feedbacks SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$feedback_id]);
        echo json_encode(["status" => "success"]);
        
    } elseif ($action === 'generate_season') {
        $data = json_decode(file_get_contents("php://input"), true);
        $title = $data['title'] ?? 'Nouvelle Saison';
        $weeks = (int)($data['weeks'] ?? 12);
        $sessionsPerWeek = (int)($data['sessionsPerWeek'] ?? 3);

        if ($weeks < 1 || $sessionsPerWeek < 1) {
            echo json_encode(["status" => "error", "message" => "Paramètres invalides"]);
            exit;
        }

        $pdo->beginTransaction();
        $stmtOrder = $pdo->query("SELECT MAX(order_num) FROM AD_seasons");
        $season_order = ((int)$stmtOrder->fetchColumn()) + 1;

        $stmtSeason = $pdo->prepare("INSERT INTO AD_seasons (title, order_num) VALUES (?, ?)");
        $stmtSeason->execute([$title, $season_order]);
        $season_id = $pdo->lastInsertId();

        $totalSessions = $weeks * $sessionsPerWeek;
        $globalSession = 1;

        $stmtWeek = $pdo->prepare("INSERT INTO AD_weeks (season_id, title, order_num) VALUES (?, ?, ?)");
        $stmtSession = $pdo->prepare("INSERT INTO AD_sessions (week_id, title, order_num) VALUES (?, ?, ?)");
        $stmtExo = $pdo->prepare("INSERT INTO AD_exercises (session_id, type, duration_seconds, order_num) VALUES (?, ?, ?, ?)");

        for ($w = 1; $w <= $weeks; $w++) {
            $stmtWeek->execute([$season_id, "Semaine $w", $w]);
            $week_id = $pdo->lastInsertId();

            for ($s = 1; $s <= $sessionsPerWeek; $s++) {
                $session_title = "Semaine $w - Entraînement $s";
                $stmtSession->execute([$week_id, $session_title, $s]);
                $session_id = $pdo->lastInsertId();

                $exo_order = 1;
                $stmtExo->execute([$session_id, 'echauffement', 300, $exo_order++]);

                $progressFactor = ($globalSession - 1) / max(1, ($totalSessions - 1));
                $runTime = round(60 + ($progressFactor * 1740)); 
                $walkTime = round(120 - ($progressFactor * 120)); 
                $coreTargetTime = (15 * 60) + round($progressFactor * 25 * 60); 
                
                $accumulated = 0;
                while ($accumulated < $coreTargetTime) {
                    $stmtExo->execute([$session_id, 'trottes', $runTime, $exo_order++]);
                    $accumulated += $runTime;

                    if ($walkTime > 15 && $accumulated < $coreTargetTime) {
                        $stmtExo->execute([$session_id, 'marches', $walkTime, $exo_order++]);
                        $accumulated += $walkTime;
                    }
                }
                $stmtExo->execute([$session_id, 'etirements', 300, $exo_order++]);
                $globalSession++;
            }
        }
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Saison générée avec succès !"]);

    } elseif ($action === 'get_all_seasons') {
        $stmt = $pdo->query("SELECT * FROM AD_seasons ORDER BY order_num ASC");
        $seasons = $stmt->fetchAll();
        foreach ($seasons as &$s) {
            $stmtW = $pdo->prepare("SELECT COUNT(*) FROM AD_weeks WHERE season_id = ?");
            $stmtW->execute([$s['id']]);
            $s['weeks_count'] = $stmtW->fetchColumn();
        }
        echo json_encode(["status" => "success", "data" => $seasons]);

    } elseif ($action === 'update_season') {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? 0;
        $title = trim($data['title'] ?? '');
        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE AD_seasons SET title = ? WHERE id = ?");
            $stmt->execute([$title, $id]);
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Données invalides"]);
        }

    } elseif ($action === 'delete_season') {
        $id = $_GET['id'] ?? 0;
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM AD_exercises WHERE session_id IN (SELECT id FROM AD_sessions WHERE week_id IN (SELECT id FROM AD_weeks WHERE season_id = ?))")->execute([$id]);
        $pdo->prepare("DELETE FROM AD_sessions WHERE week_id IN (SELECT id FROM AD_weeks WHERE season_id = ?)")->execute([$id]);
        $pdo->prepare("DELETE FROM AD_weeks WHERE season_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM AD_seasons WHERE id = ?")->execute([$id]);
        $pdo->commit();
        echo json_encode(["status" => "success"]);

    } elseif ($action === 'get_season_details') {
        // NOUVELLE ROUTE : Charger toute la hiérarchie pour le constructeur
        $season_id = $_GET['id'] ?? 0;
        $stmtW = $pdo->prepare("SELECT * FROM AD_weeks WHERE season_id = ? ORDER BY order_num");
        $stmtW->execute([$season_id]);
        $weeks = $stmtW->fetchAll();
        foreach ($weeks as &$week) {
            $stmtS = $pdo->prepare("SELECT * FROM AD_sessions WHERE week_id = ? ORDER BY order_num");
            $stmtS->execute([$week['id']]);
            $week['sessions'] = $stmtS->fetchAll();
            foreach ($week['sessions'] as &$session) {
                $stmtE = $pdo->prepare("SELECT * FROM AD_exercises WHERE session_id = ? ORDER BY order_num");
                $stmtE->execute([$session['id']]);
                $session['exercises'] = $stmtE->fetchAll();
            }
        }
        echo json_encode(["status" => "success", "data" => $weeks]);

    } elseif ($action === 'update_session_exercises') {
        // NOUVELLE ROUTE : Sauvegarder la nouvelle configuration d'un entraînement
        $data = json_decode(file_get_contents("php://input"), true);
        $session_id = $data['session_id'] ?? 0;
        $exercises = $data['exercises'] ?? [];
        
        $pdo->beginTransaction();
        // On supprime tous les anciens blocs pour insérer les nouveaux avec le bon ordre
        $pdo->prepare("DELETE FROM AD_exercises WHERE session_id = ?")->execute([$session_id]);
        $stmtExo = $pdo->prepare("INSERT INTO AD_exercises (session_id, type, duration_seconds, order_num) VALUES (?, ?, ?, ?)");
        $order = 1;
        foreach ($exercises as $exo) {
            $stmtExo->execute([$session_id, $exo['type'], $exo['duration_seconds'], $order++]);
        }
        $pdo->commit();
        echo json_encode(["status" => "success"]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur SQL : " . $e->getMessage()]);
}
?>