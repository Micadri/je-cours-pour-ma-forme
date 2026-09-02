<?php 
// Autorisation stricte pour ton domaine Vercel (CORS)
header("Access-Control-Allow-Origin: https://je-cours-pour-ma-forme-re74yj16g.vercel.app"); 
header("Content-Type: application/json; charset=UTF-8"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    exit(0); 
} 

try { 
    // Connexion à la DB Hostinger
    // IMPORTANT : Si ton PHP est hébergé SUR Hostinger, l'hôte reste souvent "localhost"
    $host = 'localhost'; // Remplace par 'srv660.hstgr.io' uniquement si localhost refuse la connexion
    $db = 'u868520261_ingrwf13_2';
    $user = 'u868520261_ingrwf13_2';
    $pass = 'Ingrwf13!';

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,         
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     
    ]); 
} catch (PDOException $e) {     
    echo json_encode(["status" => "error", "message" => "Connexion BDD échouée : " . $e->getMessage()]);     
    exit; 
}

// Fonction de vérification du Token
function requireAuth($pdo) {     
    $authHeader = null;          
    if (isset($_SERVER['Authorization'])) {         
        $authHeader = trim($_SERVER['Authorization']);     
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {         
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);     
    } elseif (function_exists('apache_request_headers')) {         
        $requestHeaders = apache_request_headers();         
        if (isset($requestHeaders['Authorization'])) {             
            $authHeader = trim($requestHeaders['Authorization']);         
        }     
    }          
    
    if (!$authHeader && isset($_GET['token'])) {         
        $authHeader = "Bearer " . trim($_GET['token']);     
    }     
    if (!$authHeader) {         
        http_response_code(401);         
        echo json_encode(["status" => "error", "message" => "Token manquant"]);         
        exit;     
    }     
    
    $token = str_replace("Bearer ", "", $authHeader);          
    $stmt = $pdo->prepare("SELECT id, role FROM AD_users WHERE api_token = ?");     
    $stmt->execute([$token]);     
    $user = $stmt->fetch();     
    if (!$user) {         
        http_response_code(401);         
        echo json_encode(["status" => "error", "message" => "Token invalide"]);         
        exit;     
    }     
    return $user; 
}
?>