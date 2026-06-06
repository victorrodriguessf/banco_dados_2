<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class AuthController
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $dados = $_POST;

        if (empty($dados)) {
            $json = json_decode(file_get_contents('php://input'), true);
            $dados = is_array($json) ? $json : [];
        }

        $usuario = $dados['usuario'] ?? '';
        $senha   = $dados['senha']   ?? '';

        if ($usuario === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Informe usuario e senha.']);
            exit();
        }

        $resultado = $this->usuarioModel->buscarPorCredenciais($usuario, $senha);

        if (!$resultado) {
            http_response_code(401);
            echo json_encode(['erro' => 'Credenciais invalidas.']);
            exit();
        }

        $token = bin2hex(random_bytes(32));

        $this->usuarioModel->salvarSessao($resultado['id'], $token);

        setcookie('auth_token', $token, [
            'expires'  => time() + (60 * 60 * 24 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict',
        ]);

        echo json_encode([
            'mensagem' => 'Login realizado com sucesso.',
            'perfil' => $resultado['perfil'],
        ]);
        exit();
    }
}
