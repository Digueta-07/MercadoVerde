<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (currentUser()) redirect(currentUser()['tipo'] === 'admin' ? 'admin/index.php' : 'index.php');
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $stmt = $pdo->prepare('SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        unset($usuario['senha']);
        $_SESSION['usuario'] = $usuario;
        redirect($usuario['tipo'] === 'admin' ? 'admin/index.php' : 'index.php');
    }
    $erro = 'E-mail ou senha inválidos.';
}
$pageTitle = 'Entrar';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-layout"><div class="auth-aside"><span class="auth-aside-mark">m</span><span class="eyebrow">BEM-VINDO AO MERCADO VERDE</span><h1>Boas compras começam com praticidade.</h1><p>Acesse sua conta para montar seu carrinho e acompanhar seus pedidos.</p><div class="auth-decoration">🥬　🥑　🍋</div></div><div class="auth-card"><span class="eyebrow">SUA CONTA</span><h2>Que bom ter você aqui!</h2><p>Entre com seus dados para continuar.</p>
<?php if ($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><label>E-mail<input type="email" name="email" required autocomplete="email" placeholder="voce@email.com"></label><label>Senha<input type="password" name="senha" required autocomplete="current-password" placeholder="Sua senha"></label><button class="button button-full" type="submit">Entrar na minha conta <span>→</span></button></form><div class="auth-foot">Ainda não tem cadastro? <a href="<?= h(url('registrar.php')) ?>">Criar conta</a></div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
