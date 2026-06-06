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

        $refreshToken = bin2hex(random_bytes(32));
        $accessToken = $this->gerarJwt($resultado);

        $this->usuarioModel->salvarSessao($resultado['id'], $refreshToken);

        setcookie('refresh_token', $refreshToken, [
            'expires'  => time() + (60 * 60 * 24 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict',
        ]);
        setcookie('auth_token', $refreshToken, [
            'expires'  => time() + (60 * 60 * 24 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict',
        ]);

        echo json_encode([
            'mensagem' => 'Login realizado com sucesso.',
            'perfil' => $resultado['perfil'],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 900,
        ]);
        exit();
    }

    public function refresh(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $dados = $this->obterDadosRequisicao();
        $refreshToken = $dados['refresh_token'] ?? $_COOKIE['refresh_token'] ?? $_COOKIE['auth_token'] ?? '';

        if ($refreshToken === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Informe o refresh_token.']);
            exit();
        }

        $usuario = $this->usuarioModel->buscarPorRefreshToken($refreshToken);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['erro' => 'Refresh token invalido.']);
            exit();
        }

        $novoRefreshToken = bin2hex(random_bytes(32));
        $accessToken = $this->gerarJwt($usuario);

        $this->usuarioModel->atualizarRefreshToken($refreshToken, $novoRefreshToken);

        setcookie('refresh_token', $novoRefreshToken, [
            'expires'  => time() + (60 * 60 * 24 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict',
        ]);
        setcookie('auth_token', $novoRefreshToken, [
            'expires'  => time() + (60 * 60 * 24 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict',
        ]);

        echo json_encode([
            'access_token' => $accessToken,
            'refresh_token' => $novoRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 900,
        ]);
        exit();
    }

    private function obterDadosRequisicao(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $json = json_decode(file_get_contents('php://input'), true);

        return is_array($json) ? $json : [];
    }

    private function gerarJwt(array $usuario): string
    {
        $agora = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'sub' => (string) $usuario['id'],
            'perfil' => $usuario['perfil'] ?? null,
            'iat' => $agora,
            'exp' => $agora + 900,
        ];

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        $assinatura = hash_hmac(
            'sha256',
            $base64Header . '.' . $base64Payload,
            $_ENV['JWT_SECRET'] ?? 'dev-secret-change-me',
            true
        );

        return $base64Header . '.' . $base64Payload . '.' . $this->base64UrlEncode($assinatura);
    }

    private function base64UrlEncode(string $dados): string
    {
        return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    }
}
