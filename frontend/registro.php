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

function cadastrarUsuario($pdo)
{
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];
    $perfil = $_POST["perfil"];

    if($perfil == "" || $perfil = null){
        return "Por favor informe o perfil do usuário";
    }
    if($usuario == "" || $usuario = null){
        return "Por favor informe o username do usuário";
    }
    if($senha == "" || $senha = null){
        return "Por favor informe a senha do usuário";
    }

    $sql = "
        INSERT INTO oficina.usuario (
            usuario,
            senha,
            perfil
        )
        VALUES (
            :usuario,
            :senha,
            :perfil
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":usuario" => $usuario,
        ":senha" => $senha,
        ":perfil" => $perfil
    ]);

    if($perfil == 'cliente'){
        header("Location: homeCliente.php");
        exit();
    } else{
        header("Location: homeFuncionario.php");
        exit();
    }
    return "Usuário cadastrado com sucesso!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem = cadastrarUsuario($pdo);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
</head>
<body>

    <h2>Cadastro de Usuário</h2>

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

        <label>Perfil:</label>
        <br>

        <select name="perfil" required>

            <option value="">
                Selecione
            </option>

            <option value="cliente">
                Cliente
            </option>

            <option value="funcionario">
                Funcionário
            </option>

        </select>

        <br><br>

        <p>
            <?php echo $mensagem; ?>
        </p>

        <button type="submit">
            Cadastrar
        </button>
        <p>Já possui uma conta? <a href="./login.php">Faça login</a></p>
    </form>

</body>
</html>