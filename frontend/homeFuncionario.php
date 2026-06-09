<?php $apiBase = 'http://localhost:8000'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel — Oficina</title>
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
            <span class="user-badge">Funcionário</span>
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
        <h2 class="page-title" id="saudacao">Olá,</h2>
        <p class="page-subtitle">Painel do funcionário — gerencie veículos, clientes e ordens de serviço.</p>
    </div>

    <!-- Cards de ação rápida -->
    <div class="cards-grid">

        <!-- Buscar Veículo -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                Buscar Veículo por Placa
            </div>

            <div class="search-row">
                <div class="form-group">
                    <label for="placa">Placa</label>
                    <input type="text" id="placa" placeholder="Ex.: ABC1D23" maxlength="8" style="text-transform:uppercase">
                </div>
                <button class="btn btn-primary" id="btnBuscarVeiculo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Buscar
                </button>
            </div>

            <div id="alertVeiculo" class="alert alert-danger hidden" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="alertVeiculoMsg"></span>
            </div>

            <div id="resultadoVeiculo" class="hidden">
                <div class="result-header">
                    <strong id="veiculoNome"></strong>
                    <span class="badge badge-blue" id="veiculoPlacaBadge"></span>
                </div>
                <div class="detail-grid" id="veiculoDetalhes"></div>
                <div id="agendamentosSection">
                    <p style="font-size:.8125rem;color:var(--text-muted);margin-bottom:.5rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Agendamentos</p>
                    <div id="agendamentosList"></div>
                </div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="card">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Clientes
            </div>

            <div class="search-row">
                <div class="form-group">
                    <label for="filtroNome">Filtrar por nome</label>
                    <input type="text" id="filtroNome" placeholder="Nome do cliente">
                </div>
                <div class="form-group">
                    <label for="filtroTipo">Tipo</label>
                    <select id="filtroTipo">
                        <option value="">Todos</option>
                        <option value="fisica">Pessoa Física</option>
                        <option value="juridica">Pessoa Jurídica</option>
                    </select>
                </div>
                <button class="btn btn-primary" id="btnListarClientes">Listar</button>
            </div>

            <div id="alertClientes" class="alert alert-danger hidden" role="alert">
                <span id="alertClientesMsg"></span>
            </div>

            <div id="resultadoClientes" class="hidden">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Documento</th>
                                <th>Telefone</th>
                                <th>E-mail</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaClientes"></tbody>
                    </table>
                </div>
                <div id="semClientes" class="empty-state hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <p>Nenhum cliente encontrado.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Card: Ordens de Serviço -->
    <div class="card">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            Registrar na Ordem de Serviço
        </div>
        <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem">
            Informe o ID do agendamento para registrar serviços, peças, pagamento ou avaliação.
        </p>

        <div class="search-row" style="align-items:flex-end">
            <div class="form-group">
                <label for="osAgendamentoId">ID do Agendamento</label>
                <input type="number" id="osAgendamentoId" placeholder="Ex.: 1" min="1">
            </div>
            <div class="form-group">
                <label for="osAcao">Ação</label>
                <select id="osAcao">
                    <option value="itens-servico">Adicionar Serviço</option>
                    <option value="itens-peca">Adicionar Peça</option>
                    <option value="pagamento">Registrar Pagamento</option>
                    <option value="avaliacao">Registrar Avaliação</option>
                </select>
            </div>
            <button class="btn btn-accent" id="btnCarregarFormOS">Abrir formulário</button>
        </div>

        <div id="alertOS" class="alert alert-danger hidden" role="alert">
            <span id="alertOSMsg"></span>
        </div>
        <div id="alertOSSuccess" class="alert alert-success hidden" role="alert">
            <span id="alertOSSuccessMsg"></span>
        </div>

        <!-- Formulários dinâmicos de OS -->
        <div id="formOS" class="hidden">

            <!-- Adicionar Serviço -->
            <div id="formItemServico" class="hidden">
                <div class="divider">Novo item de serviço</div>
                <div class="form-group">
                    <label for="osIdTipoServico">ID do Tipo de Serviço</label>
                    <input type="number" id="osIdTipoServico" min="1">
                </div>
                <div class="form-group">
                    <label for="osIdFuncionario">ID do Funcionário (opcional)</label>
                    <input type="number" id="osIdFuncionario" min="1">
                </div>
                <div class="form-group">
                    <label for="osValorMaoDeObra">Valor mão de obra (R$)</label>
                    <input type="number" id="osValorMaoDeObra" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="osObservacao">Observação (opcional)</label>
                    <input type="text" id="osObservacao" placeholder="Observações sobre o serviço">
                </div>
                <button class="btn btn-primary" id="btnEnviarItemServico">Salvar serviço</button>
            </div>

            <!-- Adicionar Peça -->
            <div id="formItemPeca" class="hidden">
                <div class="divider">Nova peça consumida</div>
                <div class="form-group">
                    <label for="osIdPeca">ID da Peça</label>
                    <input type="number" id="osIdPeca" min="1">
                </div>
                <div class="form-group">
                    <label for="osIdItemServico">ID do Item de Serviço</label>
                    <input type="number" id="osIdItemServico" min="1">
                </div>
                <div class="form-group">
                    <label for="osQuantidade">Quantidade</label>
                    <input type="number" id="osQuantidade" min="0.01" step="0.01" placeholder="1">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:.75rem">
                    <input type="checkbox" id="osDevolucao" style="width:auto;accent-color:var(--primary)">
                    <label for="osDevolucao" style="margin:0;cursor:pointer">Devolução de peça</label>
                </div>
                <button class="btn btn-primary" id="btnEnviarItemPeca">Salvar peça</button>
            </div>

            <!-- Pagamento -->
            <div id="formPagamento" class="hidden">
                <div class="divider">Registrar pagamento</div>
                <div class="form-group">
                    <label for="osIdFormaPagamento">ID da Forma de Pagamento</label>
                    <input type="number" id="osIdFormaPagamento" min="1">
                </div>
                <div class="form-group">
                    <label for="osValorPago">Valor pago (R$)</label>
                    <input type="number" id="osValorPago" min="0.01" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="osValorParcial">Valor parcial (R$, opcional)</label>
                    <input type="number" id="osValorParcial" min="0" step="0.01" placeholder="0.00">
                </div>
                <button class="btn btn-primary" id="btnEnviarPagamento">Registrar pagamento</button>
            </div>

            <!-- Avaliação -->
            <div id="formAvaliacao" class="hidden">
                <div class="divider">Registrar avaliação</div>
                <div class="form-group">
                    <label for="osAvalIdItemServico">ID do Item de Serviço</label>
                    <input type="number" id="osAvalIdItemServico" min="1">
                </div>
                <div class="form-group">
                    <label for="osNota">Nota (1 a 5)</label>
                    <select id="osNota">
                        <option value="">Selecione</option>
                        <option value="5">5 — Excelente</option>
                        <option value="4">4 — Bom</option>
                        <option value="3">3 — Regular</option>
                        <option value="2">2 — Ruim</option>
                        <option value="1">1 — Péssimo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="osComentario">Comentário (opcional)</label>
                    <input type="text" id="osComentario" placeholder="Comentário do cliente">
                </div>
                <button class="btn btn-primary" id="btnEnviarAvaliacao">Salvar avaliação</button>
            </div>

        </div>
    </div>

</main>

<script>
const API_BASE = '<?= htmlspecialchars($apiBase, ENT_QUOTES) ?>';

/* ---------- Auth ---------- */
function tokenExpirado(token) {
    try {
        const p = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
        return p.exp < Math.floor(Date.now() / 1000);
    } catch { return true; }
}

async function getToken() {
    let token = sessionStorage.getItem('access_token');
    if (token && !tokenExpirado(token)) return token;

    try {
        const r = await fetch(API_BASE + '/api/auth/refresh', { method: 'POST', credentials: 'include' });
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

/* ---------- Init ---------- */
(async function () {
    const token = await getToken();
    if (!token) return;

    const perfil = sessionStorage.getItem('perfil');
    if (perfil && perfil !== 'funcionario') {
        window.location.href = 'homeCliente.php';
        return;
    }

    const usuario = sessionStorage.getItem('usuario') || 'Funcionário';
    document.getElementById('navUsuario').textContent = usuario;
    document.getElementById('saudacao').textContent   = 'Olá, ' + usuario + '!';
})();

/* ---------- Buscar veículo ---------- */
document.getElementById('btnBuscarVeiculo').addEventListener('click', async function () {
    const placa = document.getElementById('placa').value.trim().toUpperCase();
    const alertEl  = document.getElementById('alertVeiculo');
    const resultEl = document.getElementById('resultadoVeiculo');

    alertEl.classList.add('hidden');
    resultEl.classList.add('hidden');

    if (!placa) { mostrarAlerta(alertEl, 'alertVeiculoMsg', 'Informe a placa.'); return; }

    this.disabled = true;
    this.innerHTML = '<div class="spinner"></div> Buscando...';

    try {
        const token = await getToken();
        const r = await fetch(API_BASE + '/api/veiculos/' + encodeURIComponent(placa), {
            headers: { 'Authorization': 'Bearer ' + token },
            credentials: 'include',
        });
        const data = await r.json();

        if (!r.ok) {
            mostrarAlerta(alertEl, 'alertVeiculoMsg', data.erro || 'Veículo não encontrado.');
            return;
        }

        document.getElementById('veiculoNome').textContent   = (data.marca || '') + ' ' + (data.modelo || '');
        document.getElementById('veiculoPlacaBadge').textContent = data.placa;

        document.getElementById('veiculoDetalhes').innerHTML = `
            <div class="detail-item"><label>Ano fab.</label><p>${data.ano_fabricacao || '—'}</p></div>
            <div class="detail-item"><label>Ano modelo</label><p>${data.ano_modelo || '—'}</p></div>
            <div class="detail-item"><label>Motorização</label><p>${data.motorizacao || '—'}</p></div>
            <div class="detail-item"><label>ID Cliente</label><p>${data.id_cliente || '—'}</p></div>
        `;

        const agList = document.getElementById('agendamentosList');
        if (data.agendamentos && data.agendamentos.length > 0) {
            agList.innerHTML = data.agendamentos.map(a => `
                <div style="padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:.5rem;font-size:.875rem">
                    <strong style="color:var(--primary)">#${a.id}</strong>
                    &nbsp;—&nbsp;${a.tipo_servico || '—'}
                    &nbsp;<span style="color:var(--text-muted)">${a.dt_hora_agendamento ? new Date(a.dt_hora_agendamento).toLocaleString('pt-BR') : ''}</span>
                </div>
            `).join('');
        } else {
            agList.innerHTML = '<p style="font-size:.875rem;color:var(--text-muted)">Nenhum agendamento registrado.</p>';
        }

        resultEl.classList.remove('hidden');

    } catch {
        mostrarAlerta(alertEl, 'alertVeiculoMsg', 'Erro ao buscar veículo.');
    } finally {
        this.disabled = false;
        this.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Buscar`;
    }
});

/* ---------- Listar clientes ---------- */
document.getElementById('btnListarClientes').addEventListener('click', async function () {
    const nome  = document.getElementById('filtroNome').value.trim();
    const tipo  = document.getElementById('filtroTipo').value;
    const alertEl  = document.getElementById('alertClientes');
    const resultEl = document.getElementById('resultadoClientes');

    alertEl.classList.add('hidden');
    resultEl.classList.add('hidden');

    this.disabled = true;
    this.textContent = 'Carregando...';

    try {
        const token = await getToken();
        const params = new URLSearchParams();
        if (nome) params.set('nome', nome);
        if (tipo) params.set('tipo', tipo);

        const r = await fetch(API_BASE + '/api/clientes?' + params.toString(), {
            headers: { 'Authorization': 'Bearer ' + token },
            credentials: 'include',
        });
        const data = await r.json();

        if (!r.ok) {
            mostrarAlerta(alertEl, 'alertClientesMsg', data.erro || 'Erro ao listar clientes.');
            return;
        }

        const tbody = document.getElementById('tabelaClientes');
        const semEl = document.getElementById('semClientes');

        if (!data.length) {
            tbody.innerHTML = '';
            semEl.classList.remove('hidden');
        } else {
            semEl.classList.add('hidden');
            tbody.innerHTML = data.map(c => `
                <tr>
                    <td>${htmlEsc(c.nome_completo)}</td>
                    <td>${c.cpf ? c.cpf : (c.cnpj ? c.cnpj : '—')}</td>
                    <td>${htmlEsc(c.telefone || '—')}</td>
                    <td>${htmlEsc(c.email || '—')}</td>
                </tr>
            `).join('');
        }

        resultEl.classList.remove('hidden');

    } catch {
        mostrarAlerta(alertEl, 'alertClientesMsg', 'Erro ao listar clientes.');
    } finally {
        this.disabled = false;
        this.textContent = 'Listar';
    }
});

/* ---------- OS: mostrar formulário ---------- */
document.getElementById('btnCarregarFormOS').addEventListener('click', function () {
    const acao = document.getElementById('osAcao').value;
    const id   = document.getElementById('osAgendamentoId').value;

    if (!id || id < 1) {
        mostrarAlerta(document.getElementById('alertOS'), 'alertOSMsg', 'Informe o ID do agendamento.');
        return;
    }

    document.getElementById('alertOS').classList.add('hidden');
    document.getElementById('alertOSSuccess').classList.add('hidden');

    ['formItemServico', 'formItemPeca', 'formPagamento', 'formAvaliacao'].forEach(f =>
        document.getElementById(f).classList.add('hidden')
    );

    const mapaForm = {
        'itens-servico': 'formItemServico',
        'itens-peca':    'formItemPeca',
        'pagamento':     'formPagamento',
        'avaliacao':     'formAvaliacao',
    };

    document.getElementById(mapaForm[acao]).classList.remove('hidden');
    document.getElementById('formOS').classList.remove('hidden');
});

/* ---------- OS: enviar item de serviço ---------- */
document.getElementById('btnEnviarItemServico').addEventListener('click', () => enviarOS('itens-servico', {
    id_tipo_servico:   () => parseInt(document.getElementById('osIdTipoServico').value) || null,
    id_funcionario:    () => parseInt(document.getElementById('osIdFuncionario').value)  || null,
    valor_mao_de_obra: () => parseFloat(document.getElementById('osValorMaoDeObra').value) || null,
    observacao:        () => document.getElementById('osObservacao').value.trim() || null,
}));

/* ---------- OS: enviar peça ---------- */
document.getElementById('btnEnviarItemPeca').addEventListener('click', () => enviarOS('itens-peca', {
    id_peca:         () => parseInt(document.getElementById('osIdPeca').value)         || null,
    id_item_servico: () => parseInt(document.getElementById('osIdItemServico').value)  || null,
    quantidade:      () => parseFloat(document.getElementById('osQuantidade').value)   || null,
    devolucao:       () => document.getElementById('osDevolucao').checked,
}));

/* ---------- OS: registrar pagamento ---------- */
document.getElementById('btnEnviarPagamento').addEventListener('click', () => enviarOS('pagamento', {
    id_forma_pagamento: () => parseInt(document.getElementById('osIdFormaPagamento').value) || null,
    valor_pago:         () => parseFloat(document.getElementById('osValorPago').value)      || null,
    valor_parcial:      () => parseFloat(document.getElementById('osValorParcial').value)   || 0,
}));

/* ---------- OS: registrar avaliação ---------- */
document.getElementById('btnEnviarAvaliacao').addEventListener('click', () => enviarOS('avaliacao', {
    id_item_servico: () => parseInt(document.getElementById('osAvalIdItemServico').value) || null,
    nota:            () => parseInt(document.getElementById('osNota').value)              || null,
    comentario:      () => document.getElementById('osComentario').value.trim() || null,
}));

async function enviarOS(acao, camposGetters) {
    const id = document.getElementById('osAgendamentoId').value;
    const alertErr = document.getElementById('alertOS');
    const alertOk  = document.getElementById('alertOSSuccess');

    alertErr.classList.add('hidden');
    alertOk.classList.add('hidden');

    const body = {};
    for (const [k, getter] of Object.entries(camposGetters)) {
        body[k] = getter();
    }

    try {
        const token = await getToken();
        const r = await fetch(`${API_BASE}/api/agendamentos/${id}/${acao}`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body),
        });
        const data = await r.json();

        if (!r.ok) {
            mostrarAlerta(alertErr, 'alertOSMsg', data.erro || 'Erro ao processar a requisição.');
            return;
        }

        alertOk.classList.remove('hidden');
        document.getElementById('alertOSSuccessMsg').textContent = data.mensagem || 'Operação realizada com sucesso.';

    } catch {
        mostrarAlerta(alertErr, 'alertOSMsg', 'Não foi possível conectar ao servidor.');
    }
}

/* ---------- Utilitários ---------- */
function mostrarAlerta(el, msgId, msg) {
    document.getElementById(msgId).textContent = msg;
    el.classList.remove('hidden');
}

function htmlEsc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

</body>
</html>
