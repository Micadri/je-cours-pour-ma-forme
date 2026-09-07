<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);
$data = json_decode(file_get_contents("php://input"), true);

$first_name = trim($data['first_name'] ?? '');
$audio_enabled = isset($data['audio_enabled']) ? (int)$data['audio_enabled'] : 1;
$theme = $data['theme'] ?? 'light';
$avatar = $data['avatar'] ?? null;

try {
    if ($avatar) {
        $stmt = $pdo->prepare("UPDATE AD_users SET first_name = ?, audio_enabled = ?, theme = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$first_name, $audio_enabled, $theme, $avatar, $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE AD_users SET first_name = ?, audio_enabled = ?, theme = ? WHERE id = ?");
        $stmt->execute([$first_name, $audio_enabled, $theme, $user['id']]);
    }
    echo json_encode(["status" => "success", "message" => "Profil mis à jour"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>