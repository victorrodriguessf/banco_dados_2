<?php
$host   = 'db.rtfvkyqzqzvxhbmockcj.supabase.co';
$port   = '5432';
$dbname = 'postgres';
$user   = 'postgres';
$password = 'aNNcMLRr@5*+sk7';

$erro    = '';
$sucesso = '';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';
    $perfil  = $_POST['perfil'] ?? '';

    if ($usuario === '' || $senha === '' || $perfil === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!in_array($perfil, ['cliente', 'funcionario'])) {
        $erro = 'Perfil inválido.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO oficina.usuario (usuario, senha, perfil) VALUES (:usuario, :senha, :perfil)');
            $stmt->execute([':usuario' => $usuario, ':senha' => $senha, ':perfil' => $perfil]);
            header('Location: login.php?cadastro=ok');
            exit();
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23505'
                ? 'Esse nome de usuário já está em uso.'
                : 'Erro ao cadastrar. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — Oficina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <div class="auth-logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <div class="auth-logo-text">Oficina<span>Pro</span></div>
        </div>

        <h1 class="auth-title">Criar conta</h1>
        <p class="auth-subtitle">Preencha os dados abaixo para se cadastrar no sistema.</p>

        <?php if ($erro !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= htmlspecialchars($erro) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Escolha um nome de usuário"
                    value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Crie uma senha"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="toggle-password" aria-label="Mostrar senha" id="toggleSenha">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="perfil">Perfil</label>
                <select id="perfil" name="perfil" required>
                    <option value="">Selecione seu perfil</option>
                    <option value="cliente"      <?= ($_POST['perfil'] ?? '') === 'cliente'      ? 'selected' : '' ?>>Cliente</option>
                    <option value="funcionario"  <?= ($_POST['perfil'] ?? '') === 'funcionario'  ? 'selected' : '' ?>>Funcionário</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Cadastrar
            </button>
        </form>

        <div class="auth-footer">
            Já possui uma conta? <a href="login.php">Faça login</a>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleSenha').addEventListener('click', function () {
    const input = document.getElementById('senha');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.setAttribute('aria-label', input.type === 'password' ? 'Mostrar senha' : 'Ocultar senha');
});
</script>

</body>
</html>
