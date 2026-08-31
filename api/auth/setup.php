<?php
require '../config.php';

$email = 'adrien@test.be';
$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    // Nettoyer l'ancien compte s'il existe
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    
    // Insérer le compte avec un hash 100% compatible avec ton PHP
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$email, $hash]);
    
    echo "Compte administrateur prêt ! Tu peux tester la connexion dans Insomnia.";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>