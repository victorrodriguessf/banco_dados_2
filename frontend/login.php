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

        // Verifica o perfil
        if ($resultado["perfil"] == "funcionario") {

            header("Location: homeFuncionario.php");
            exit();
        } else{
            header("Location: homeCliente.php");
            exit();
        }

        return "Login realizado com sucesso!";

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