<?php

use App\Controllers\AuthController;

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'POST' && $uri === '/auth/login') {
    (new AuthController())->login();
    exit();
}

if ($method === 'POST' && $uri === '/auth/refresh') {
    (new AuthController())->refresh();
    exit();
}

header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode(['erro' => 'Rota nao encontrada.']);
