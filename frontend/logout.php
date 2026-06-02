<?php

$host = "db.rtfvkyqzqzvxhbmockcj.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "aNNcMLRr@5*+sk7";

try {

    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Erro na conexão: " . $e->getMessage());
}

// Obtém o token do cookie
$token = $_COOKIE["auth_token"] ?? null;

if ($token) {

    // Remove a sessão do banco
    $stmt = $pdo->prepare("
        DELETE
        FROM oficina.sessao
        WHERE token = :token
    ");

    $stmt->execute([
        ":token" => $token
    ]);
}

// Remove o cookie
setcookie(
    "auth_token",
    "",
    [
        "expires" => time() - 3600,
        "path" => "/"
    ]
);

// Redireciona para o login
header("Location: login.php");
exit();