<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

// Carrega variáveis do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/src/Routes/api.php';