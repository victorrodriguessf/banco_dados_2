<?php

use App\Controllers\AuthController;

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'POST' && $uri === '/auth/login') {
    (new AuthController())->login();
    exit();
}

// Rota não encontrada
http_response_code(404);
echo json_encode(['erro' => 'Rota não encontrada.']);