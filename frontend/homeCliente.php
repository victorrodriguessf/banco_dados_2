<?php $apiBase = 'http://localhost:8000'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente — Oficina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="#" class="navbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
        Oficina<span>Pro</span>
    </a>

    <div class="navbar-right">
        <div class="navbar-user">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span id="navUsuario">Carregando...</span>
            <span class="user-badge">Cliente</span>
        </div>
        <a href="logout.php" class="btn-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sair
        </a>
    </div>
</nav>

<!-- Conteúdo principal -->
<main class="page-content">

    <div class="page-header">
        <h2 class="page-title" id="saudacao">Olá!</h2>
        <p class="page-subtitle">Bem-vindo à sua área de cliente.</p>
    </div>

    <div class="cards-grid">

        <!-- Meus dados -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Minha conta
            </div>
            <div class="detail-grid" style="grid-template-columns: 1fr;">
                <div class="detail-item">
                    <label>Usuário</label>
                    <p id="infoUsuario">—</p>
                </div>
                <div class="detail-item">
                    <label>Perfil</label>
                    <p><span class="badge badge-blue">Cliente</span></p>
                </div>
            </div>
        </div>

        <!-- Meus agendamentos -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Meus Agendamentos
            </div>
            <div id="agendamentosList" class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p>Carregando seus agendamentos...</p>
            </div>
        </div>

        <!-- Adicionar agendamento -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10h-3V7a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v3H3"/>
                    <path d="M16 21h5V11H16"/>
                    <path d="M3 10h5v11H3z"/>
                </svg>
                Adicionar Agendamento
            </div>

            <div id="alertAgendamento" class="alert alert-danger hidden">
                <span id="alertAgendamentoMsg"></span>
            </div>
            <div id="alertAgendamentoOk" class="alert alert-success hidden">
                <span id="alertAgendamentoOkMsg"></span>
            </div>

            <div class="form-group">
                <label for="agendamentoVeiculo">Veículo</label>
                <select id="agendamentoVeiculo">
                    <option value="">Selecione um veículo</option>
                </select>
            </div>
            <div class="form-group">
                <label for="agendamentoTipoServico">ID do Tipo de Serviço</label>
                <input type="number" id="agendamentoTipoServico" placeholder="Ex.: 1" min="1">
            </div>
            <div class="form-group">
                <label for="agendamentoDataHora">Data e hora do atendimento</label>
                <input type="datetime-local" id="agendamentoDataHora">
            </div>
            <div class="form-group">
                <label for="agendamentoHodometro">Hodômetro inicial</label>
                <input type="number" id="agendamentoHodometro" placeholder="Ex.: 12345" min="0">
            </div>
            <p style="font-size:.86rem;color:var(--text-muted);margin-bottom:1rem;">
                Informe o ID do tipo de serviço desejado. Se não souber o ID, peça ao atendimento.
            </p>
            <button class="btn btn-primary btn-full" id="btnAdicionarAgendamento">Agendar</button>
        </div>

        <!-- Meus veículos -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                Meus Veículos
            </div>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                <p>Veículos vinculados aparecerão aqui.</p>
            </div>
        </div>

        <!-- Avaliar serviço -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                Avaliar Serviço
            </div>

            <div id="alertAvalCliente" class="alert alert-danger hidden">
                <span id="alertAvalClienteMsg"></span>
            </div>
            <div id="alertAvalClienteOk" class="alert alert-success hidden">
                <span id="alertAvalClienteOkMsg"></span>
            </div>

            <div class="form-group">
                <label for="avalAgendamentoId">ID do Agendamento</label>
                <input type="number" id="avalAgendamentoId" placeholder="Ex.: 1" min="1">
            </div>
            <div class="form-group">
                <label for="avalIdItemServico">ID do Item de Serviço</label>
                <input type="number" id="avalIdItemServico" placeholder="Ex.: 1" min="1">
            </div>
            <div class="form-group">
                <label for="avalNota">Nota</label>
                <select id="avalNota">
                    <option value="">Selecione</option>
                    <option value="5">5 — Excelente</option>
                    <option value="4">4 — Bom</option>
                    <option value="3">3 — Regular</option>
                    <option value="2">2 — Ruim</option>
                    <option value="1">1 — Péssimo</option>
                </select>
            </div>
            <div class="form-group">
                <label for="avalComentario">Comentário (opcional)</label>
                <input type="text" id="avalComentario" placeholder="Como foi sua experiência?">
            </div>
            <button class="btn btn-accent btn-full" id="btnEnviarAvalCliente">Enviar avaliação</button>
        </div>

    </div>
</main>

<script>
const API_BASE = '<?= htmlspecialchars($apiBase, ENT_QUOTES) ?>';

function tokenExpirado(token) {
    try {
        const p = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
        return p.exp < Math.floor(Date.now() / 1000);
    } catch { return true; }
}

function parseJwt(token) {
    try {
        const payload = token.split('.')[1];
        return JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/')));
    } catch {
        return null;
    }
}

function obterIdClienteDoToken(token) {
    const payload = parseJwt(token);
    const sub = payload?.sub;
    return sub ? parseInt(sub, 10) : null;
}

async function getToken() {
    let token = sessionStorage.getItem('access_token');
    if (token && !tokenExpirado(token)) return token;

    try {
        const r = await fetch(API_BASE + '/auth/refresh', { method: 'POST', credentials: 'include' });
        if (!r.ok) throw new Error();
        const d = await r.json();
        sessionStorage.setItem('access_token', d.access_token);
        return d.access_token;
    } catch {
        sessionStorage.clear();
        window.location.href = 'login.php';
        return null;
    }
}

async function carregarCliente() {
    const token = await getToken();
    if (!token) return null;

    const clienteId = obterIdClienteDoToken(token);
    if (!clienteId) return null;

    try {
        const response = await fetch(`${API_BASE}/clientes/${clienteId}`, {
            headers: { 'Authorization': 'Bearer ' + token },
            credentials: 'include',
        });

        if (!response.ok) return null;
        return await response.json();
    } catch {
        return null;
    }
}

function atualizarCarregamentoAgendamentos(html) {
    const container = document.getElementById('agendamentosList');
    container.classList.remove('empty-state');
    container.innerHTML = html;
}

async function listarAgendamentos() {
    const token = await getToken();
    if (!token) return;

    try {
        const response = await fetch(`${API_BASE}/agendamentos`, {
            headers: { 'Authorization': 'Bearer ' + token },
            credentials: 'include',
        });

        if (!response.ok) {
            throw new Error();
        }

        const data = await response.json();
        const agendamentos = Array.isArray(data.agendamentos) ? data.agendamentos : [];

        if (agendamentos.length === 0) {
            atualizarCarregamentoAgendamentos('<p style="font-size:.875rem;color:var(--text-muted)">Nenhum agendamento registrado.</p>');
            return;
        }

        const html = agendamentos.map(a => `
            <div class="agendamento-item">
                <strong>Agendamento #${a.id}</strong>
                <span>${a.dt_hora_agendamento ? new Date(a.dt_hora_agendamento).toLocaleString('pt-BR') : 'Data indisponível'}</span>
                <span>Veículo: ${a.marca || '—'} ${a.modelo || '—'} (${a.placa || '—'})</span>
                <span>Serviço: ${a.tipo_servico_descricao || '—'}</span>
            </div>
        `).join('');

        atualizarCarregamentoAgendamentos(html);
    } catch {
        atualizarCarregamentoAgendamentos('<p style="font-size:.875rem;color:var(--text-muted)">Não foi possível carregar os agendamentos.</p>');
    }
}

async function carregarVeiculosDoCliente() {
    const cliente = await carregarCliente();
    const select = document.getElementById('agendamentoVeiculo');

    select.innerHTML = '<option value="">Selecione um veículo</option>';

    if (!cliente?.veiculos?.length) {
        select.innerHTML += '<option value="">Nenhum veículo cadastrado</option>';
        return;
    }

    cliente.veiculos.forEach(veiculo => {
        const option = document.createElement('option');
        option.value = veiculo.id;
        option.textContent = `${veiculo.marca || 'Sem marca'} ${veiculo.modelo || ''} (${veiculo.placa || 'sem placa'})`;
        select.appendChild(option);
    });
}

(async function () {
    const token = await getToken();
    if (!token) return;

    const perfil = sessionStorage.getItem('perfil');
    if (perfil && perfil === 'funcionario') {
        window.location.href = 'homeFuncionario.php';
        return;
    }

    const usuario = sessionStorage.getItem('usuario') || 'Cliente';
    document.getElementById('navUsuario').textContent  = usuario;
    document.getElementById('saudacao').textContent    = 'Olá, ' + usuario + '!';
    document.getElementById('infoUsuario').textContent = usuario;

    await carregarVeiculosDoCliente();
    await listarAgendamentos();
})();

/* ---------- Agendamento do cliente ---------- */
document.getElementById('btnAdicionarAgendamento').addEventListener('click', async function () {
    const alertErr = document.getElementById('alertAgendamento');
    const alertOk  = document.getElementById('alertAgendamentoOk');

    alertErr.classList.add('hidden');
    alertOk.classList.add('hidden');

    const token = await getToken();
    if (!token) return;

    const clienteId = obterIdClienteDoToken(token);
    const idVeiculo = parseInt(document.getElementById('agendamentoVeiculo').value, 10) || null;
    const idTipoServico = parseInt(document.getElementById('agendamentoTipoServico').value, 10) || null;
    const dtHoraAgendamento = document.getElementById('agendamentoDataHora').value;
    const hodometroInicial = parseInt(document.getElementById('agendamentoHodometro').value, 10);

    if (!clienteId || !idVeiculo || !idTipoServico || !dtHoraAgendamento || Number.isNaN(hodometroInicial)) {
        document.getElementById('alertAgendamentoMsg').textContent = 'Preencha todos os campos do agendamento.';
        alertErr.classList.remove('hidden');
        return;
    }

    this.disabled = true;
    this.textContent = 'Agendando...';

    try {
        const response = await fetch(`${API_BASE}/agendamentos`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                id_cliente: clienteId,
                id_veiculo: idVeiculo,
                id_tipo_servico: idTipoServico,
                dt_hora_agendamento: dtHoraAgendamento,
                hodometro_inicial: hodometroInicial,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            document.getElementById('alertAgendamentoMsg').textContent = data.erro || 'Erro ao criar agendamento.';
            alertErr.classList.remove('hidden');
            return;
        }

        document.getElementById('alertAgendamentoOkMsg').textContent = 'Agendamento criado com sucesso.';
        alertOk.classList.remove('hidden');
        document.getElementById('agendamentoTipoServico').value = '';
        document.getElementById('agendamentoDataHora').value = '';
        document.getElementById('agendamentoHodometro').value = '';
        document.getElementById('agendamentoVeiculo').selectedIndex = 0;
        await listarAgendamentos();
    } catch {
        document.getElementById('alertAgendamentoMsg').textContent = 'Não foi possível conectar ao servidor.';
        alertErr.classList.remove('hidden');
    } finally {
        this.disabled = false;
        this.textContent = 'Agendar';
    }
});

/* ---------- Avaliação do cliente ---------- */
document.getElementById('btnEnviarAvalCliente').addEventListener('click', async function () {
    const agendamentoId = document.getElementById('avalAgendamentoId').value;
    const idItemServico = parseInt(document.getElementById('avalIdItemServico').value) || null;
    const nota          = parseInt(document.getElementById('avalNota').value)          || null;
    const comentario    = document.getElementById('avalComentario').value.trim() || null;

    const alertErr = document.getElementById('alertAvalCliente');
    const alertOk  = document.getElementById('alertAvalClienteOk');

    alertErr.classList.add('hidden');
    alertOk.classList.add('hidden');

    if (!agendamentoId || !idItemServico || !nota) {
        document.getElementById('alertAvalClienteMsg').textContent = 'Preencha ID do agendamento, ID do item de serviço e nota.';
        alertErr.classList.remove('hidden');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<div class="spinner"></div> Enviando...';

    try {
        const token = await getToken();
        const r = await fetch(`${API_BASE}/agendamentos/${agendamentoId}/avaliacao`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id_item_servico: idItemServico, nota, comentario }),
        });
        const data = await r.json();

        if (!r.ok) {
            document.getElementById('alertAvalClienteMsg').textContent = data.erro || 'Erro ao enviar avaliação.';
            alertErr.classList.remove('hidden');
            return;
        }

        document.getElementById('alertAvalClienteOkMsg').textContent = 'Avaliação enviada com sucesso!';
        alertOk.classList.remove('hidden');
        document.getElementById('avalAgendamentoId').value  = '';
        document.getElementById('avalIdItemServico').value  = '';
        document.getElementById('avalNota').value           = '';
        document.getElementById('avalComentario').value     = '';

    } catch {
        document.getElementById('alertAvalClienteMsg').textContent = 'Não foi possível conectar ao servidor.';
        alertErr.classList.remove('hidden');
    } finally {
        this.disabled = false;
        this.textContent = 'Enviar avaliação';
    }
});
</script>

</body>
</html>
