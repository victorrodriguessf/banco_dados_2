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

$token = $_COOKIE["auth_token"];

$sql = "
    SELECT u.*
    FROM oficina.sessao s
    INNER JOIN oficina.usuario u
        ON u.id = s.usuario_id
    WHERE s.token = :token
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":token" => $token
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$usuario){
    header("Location: login.php");
    exit();
}

?>

<?php if ($usuario): ?>

    <p>Usuário logado: <?= htmlspecialchars($usuario["usuario"]) ?></p>

<?php else: ?>

    <p>Usuário não autenticado.</p>

<?php endif; ?>

<form action="logout.php" method="POST">
    <button type="submit">
        Sair
    </button>
</form>