<?php
$pageTitle = $pageTitle ?? 'Mercado Verde';
$activePage = $activePage ?? '';
$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Mercado Verde</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= h(url('index.php')) ?>">
            <span class="brand-mark">m</span>
            <span>mercado<span class="brand-light">verde</span><small>fresquinho todo dia</small></span>
        </a>
        <nav class="main-nav" aria-label="Navegação principal">
            <a class="<?= $activePage === 'loja' ? 'active' : '' ?>" href="<?= h(url('index.php')) ?>">Início</a>
            <?php if ($user && $user['tipo'] === 'cliente'): ?>
                <a class="<?= $activePage === 'pedidos' ? 'active' : '' ?>" href="<?= h(url('pedidos.php')) ?>">Meus pedidos</a>
            <?php endif; ?>
            <?php if ($user && $user['tipo'] === 'admin'): ?>
                <a href="<?= h(url('admin/index.php')) ?>">Painel administrativo</a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <?php if ($user): ?>
                <span class="welcome-user">Olá, <?= h(explode(' ', $user['nome'])[0]) ?></span>
                <?php if ($user['tipo'] === 'cliente'): ?>
                    <a class="icon-button cart-link" href="<?= h(url('carrinho.php')) ?>" aria-label="Carrinho">🛒<span><?= cartCount() ?></span></a>
                <?php endif; ?>
                <a class="login-link" href="<?= h(url('logout.php')) ?>">Sair</a>
            <?php else: ?>
                <a class="login-link" href="<?= h(url('login.php')) ?>">Entrar</a>
                <a class="button button-small" href="<?= h(url('registrar.php')) ?>">Criar conta</a>
            <?php endif; ?>
        </div>
        <button class="mobile-menu-button" type="button" aria-label="Abrir menu" onclick="document.body.classList.toggle('menu-open')">☰</button>
    </div>
</header>
<main class="page-shell">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
    <?php endif; ?>
