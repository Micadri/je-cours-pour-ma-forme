<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

$data = json_decode(file_get_contents("php://input"), true);
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

if (!$subject || !$message) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs."]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $subject, $message]);
    echo json_encode(["status" => "success", "message" => "Merci pour votre retour !"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>