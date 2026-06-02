<?php

$host = "db.rtfvkyqzqzvxhbmockcj.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "aNNcMLRr@5*+sk7";

$mensagem = "";

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

function realizarLogin($pdo)
{
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];

    $sql = "
        SELECT *
        FROM oficina.usuario
        WHERE usuario = :usuario
        AND senha = :senha
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":usuario" => $usuario,
        ":senha" => $senha
    ]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {

        // Gera token seguro
        $token = bin2hex(random_bytes(32));

        // Salva sessão no banco
        $sqlSessao = "
            INSERT INTO oficina.sessao
            (
                usuario_id,
                token
            )
            VALUES
            (
                :usuario_id,
                :token
            )
        ";

        $stmtSessao = $pdo->prepare($sqlSessao);

        $stmtSessao->execute([
            ":usuario_id" => $resultado["id"],
            ":token" => $token
        ]);

        // Salva token em cookie
        setcookie(
            "auth_token",
            $token,
            [
                "expires" => time() + (60 * 60 * 24 * 7), // 7 dias
                "path" => "/",
                "httponly" => true,
                "secure" => true,
                "samesite" => "Strict"
            ]
        );

        if ($resultado["perfil"] == "funcionario") {
            header("Location: homeFuncionario.php");
            exit();
        } else {
            header("Location: homeCliente.php");
            exit();
        }
    } else {
        return "Credenciais inválidas!";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem = realizarLogin($pdo);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

    <h2>Login</h2>

    <form method="POST">

        <label>Usuário:</label>
        <br>

        <input
            type="text"
            name="usuario"
            required
        >

        <br><br>

        <label>Senha:</label>
        <br>

        <input
            type="password"
            name="senha"
            required
        >

        <br><br>

        <p>
            <?php echo $mensagem; ?>
        </p>

        <button type="submit">
            Entrar
        </button>

        <p>Não possui cadastro? <a href="./registro.php">Crie uma conta</a></p>

    </form>

</body>
</html>