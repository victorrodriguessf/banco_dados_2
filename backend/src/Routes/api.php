<?php

use App\Controllers\AgendamentoController;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\VeiculoController;

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && preg_match('#^/veiculos/([^/]+)$#', $uri, $m)) {
    (new VeiculoController())->buscar($m[1]);
    exit();
}

if ($method === 'POST' && $uri === '/veiculos') {
    (new VeiculoController())->cadastrar();
    exit();
}

if ($method === 'POST' && preg_match('#^/agendamentos/?$#', $uri)) {
    (new AgendamentoController())->abrirOrdemServico();
    exit();
}

if ($method === 'GET' && preg_match('#^/agendamentos/?$#', $uri)) {
    (new AgendamentoController())->listarOrdensServico();
    exit();
}

if ($method === 'PATCH' && preg_match('#^/agendamentos/(\d+)/status/?$#', $uri, $matches)) {
    (new AgendamentoController())->atualizarStatusOrdemServico((int) $matches[1]);
    exit();
}

if ($method === 'POST' && $uri === '/auth/login') {
    (new AuthController())->login();
    exit();
}

if ($method === 'POST' && $uri === '/auth/refresh') {
    (new AuthController())->refresh();
    exit();
}

if ($method === 'POST' && $uri === '/clientes') {
    (new ClienteController())->criar();
    exit();
}

if ($method === 'GET' && preg_match('#^/clientes$#', $uri)) {
    (new ClienteController())->listar();
    exit();
}

if ($method === 'GET' && preg_match('#^/clientes/(\d+)$#', $uri, $matches)) {
    (new ClienteController())->buscarPorId((int) $matches[1]);
    exit();
}

if ($method === 'POST' && preg_match('#^/agendamentos/(\d+)/itens-servico$#', $uri, $m)) {
    (new AgendamentoController())->adicionarItemServico((int) $m[1]);
    exit();
}

if ($method === 'POST' && preg_match('#^/agendamentos/(\d+)/itens-peca$#', $uri, $m)) {
    (new AgendamentoController())->adicionarItemPeca((int) $m[1]);
    exit();
}

if ($method === 'POST' && preg_match('#^/agendamentos/(\d+)/pagamento$#', $uri, $m)) {
    (new AgendamentoController())->registrarPagamento((int) $m[1]);
    exit();
}

if ($method === 'POST' && preg_match('#^/agendamentos/(\d+)/avaliacao$#', $uri, $m)) {
    (new AgendamentoController())->registrarAvaliacao((int) $m[1]);
    exit();
}

header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode(['erro' => 'Rota nao encontrada.']);
