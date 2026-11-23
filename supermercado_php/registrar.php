<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (currentUser()) redirect('index.php');
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
        $erro = 'Informe seu nome, um e-mail válido e uma senha com pelo menos 6 caracteres.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, "cliente")');
            $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            setFlash('Cadastro realizado! Entre com seu e-mail e senha.');
            redirect('login.php');
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23000' ? 'Este e-mail já está cadastrado.' : 'Não foi possível concluir o cadastro.';
        }
    }
}
$pageTitle = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-layout"><div class="auth-aside"><span class="auth-aside-mark">m</span><span class="eyebrow">CADASTRO RÁPIDO</span><h1>Seu mercado favorito, na palma da mão.</h1><p>Crie sua conta e tenha seus pedidos organizados em um só lugar.</p><div class="auth-decoration">🍅　🥦　🥕</div></div><div class="auth-card"><span class="eyebrow">NOVA CONTA</span><h2>Vamos começar?</h2><p>Preencha seus dados para criar seu acesso.</p>
<?php if ($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><label>Nome completo<input type="text" name="nome" required maxlength="120" value="<?= h($_POST['nome'] ?? '') ?>" placeholder="Seu nome"></label><label>E-mail<input type="email" name="email" required maxlength="160" value="<?= h($_POST['email'] ?? '') ?>" placeholder="voce@email.com"></label><label>Senha<input type="password" name="senha" required minlength="6" placeholder="Mínimo de 6 caracteres"></label><button class="button button-full" type="submit">Criar minha conta <span>→</span></button></form><div class="auth-foot">Já tem cadastro? <a href="<?= h(url('login.php')) ?>">Entrar</a></div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
