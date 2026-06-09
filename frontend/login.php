<?php
// URL base da API — ajuste conforme o ambiente
$apiBase = 'http://localhost:8000';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Oficina</title>
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

        <h1 class="auth-title">Bem-vindo de volta</h1>
        <p class="auth-subtitle">Informe suas credenciais para acessar o sistema.</p>

        <div id="alert" class="alert alert-danger hidden" role="alert" aria-live="polite">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="alertMsg"></span>
        </div>

        <form id="loginForm" novalidate>
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Digite seu usuário"
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
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-password" aria-label="Mostrar senha" id="toggleSenha">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                Entrar
            </button>
        </form>

        <div class="auth-footer">
            Não possui cadastro? <a href="registro.php">Crie uma conta</a>
        </div>
    </div>
</div>

<script>
const API_BASE = '<?= htmlspecialchars($apiBase, ENT_QUOTES) ?>';

// Redireciona se já estiver autenticado e token ainda válido
(function () {
    const token = sessionStorage.getItem('access_token');
    if (token && !tokenExpirado(token)) {
        const perfil = sessionStorage.getItem('perfil');
        window.location.href = perfil === 'funcionario' ? 'homeFuncionario.php' : 'homeCliente.php';
    }
})();

function tokenExpirado(token) {
    try {
        const payload = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
        return payload.exp < Math.floor(Date.now() / 1000);
    } catch { return true; }
}

// Toggle visibilidade da senha
document.getElementById('toggleSenha').addEventListener('click', function () {
    const input = document.getElementById('senha');
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    this.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
    document.getElementById('eyeIcon').innerHTML = isPassword
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
});

function mostrarErro(msg) {
    const alert = document.getElementById('alert');
    document.getElementById('alertMsg').textContent = msg;
    alert.classList.remove('hidden');
}

function ocultarErro() {
    document.getElementById('alert').classList.add('hidden');
}

function setCarregando(sim) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = sim;
    btn.innerHTML = sim
        ? '<div class="spinner"></div> Entrando...'
        : 'Entrar';
}

document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    ocultarErro();

    const usuario = document.getElementById('usuario').value.trim();
    const senha   = document.getElementById('senha').value;

    if (!usuario || !senha) {
        mostrarErro('Preencha usuário e senha.');
        return;
    }

    setCarregando(true);

    try {
        const resp = await fetch(API_BASE + '/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ usuario, senha }),
        });

        const data = await resp.json();

        if (!resp.ok) {
            mostrarErro(data.erro || 'Erro ao realizar login.');
            return;
        }

        sessionStorage.setItem('access_token', data.access_token);
        sessionStorage.setItem('perfil',        data.perfil);
        sessionStorage.setItem('usuario',        usuario);

        window.location.href = data.perfil === 'funcionario' ? 'homeFuncionario.php' : 'homeCliente.php';

    } catch {
        mostrarErro('Não foi possível conectar ao servidor. Tente novamente.');
    } finally {
        setCarregando(false);
    }
});
</script>

</body>
</html>
