<?php
// Expira os cookies no browser
foreach (['auth_token', 'refresh_token'] as $cookieName) {
    setcookie($cookieName, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Saindo...</title>
</head>
<body>
<script>
    sessionStorage.clear();
    window.location.replace('login.php');
</script>
</body>
</html>
