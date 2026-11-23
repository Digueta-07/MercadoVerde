<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$stats = [
    'produtos' => (int) $pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')->fetchColumn(),
    'estoque_baixo' => (int) $pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND estoque <= estoque_minimo')->fetchColumn(),
    'pedidos' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
    'vendas' => (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE status <> 'Cancelado'")->fetchColumn(),
];
$ultimos = $pdo->query('SELECT p.*, u.nome AS cliente FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.criado_em DESC LIMIT 6')->fetchAll();
$pageTitle = 'Painel administrativo';
require __DIR__ . '/../includes/header.php';
?>
<div class="admin-welcome"><div><span class="eyebrow">ÁREA DE GESTÃO</span><h1>Olá, <?= h(explode(' ', currentUser()['nome'])[0]) ?> 👋</h1><p>Acompanhe o movimento da sua loja por aqui.</p></div><a href="<?= h(url('admin/produtos.php')) ?>" class="button">+ Cadastrar produto</a></div>
<div class="admin-nav"><a class="admin-nav-active" href="<?= h(url('admin/index.php')) ?>">Visão geral</a><a href="<?= h(url('admin/produtos.php')) ?>">Produtos e estoque</a><a href="<?= h(url('admin/pedidos.php')) ?>">Pedidos</a><a href="<?= h(url('index.php')) ?>">Ver loja ↗</a></div>
<div class="stats-grid"><div class="stat-card"><span class="stat-icon green">🛍️</span><small>Produtos ativos</small><strong><?= $stats['produtos'] ?></strong><span class="stat-caption">cadastrados na loja</span></div><div class="stat-card"><span class="stat-icon orange">📦</span><small>Estoque baixo</small><strong><?= $stats['estoque_baixo'] ?></strong><span class="stat-caption">no mínimo ou abaixo</span></div><div class="stat-card"><span class="stat-icon blue">🧾</span><small>Pedidos recebidos</small><strong><?= $stats['pedidos'] ?></strong><span class="stat-caption">desde o início</span></div><div class="stat-card"><span class="stat-icon purple">💰</span><small>Total em pedidos</small><strong><?= money($stats['vendas']) ?></strong><span class="stat-caption">exceto cancelados</span></div></div>
<section class="admin-section"><div class="section-heading"><div><span class="eyebrow">MOVIMENTAÇÃO</span><h2>Pedidos recentes</h2><p>Veja as compras que chegaram à loja.</p></div><a class="text-link" href="<?= h(url('admin/pedidos.php')) ?>">Ver todos →</a></div>
<?php if (!$ultimos): ?><div class="empty-panel">Ainda não existem pedidos registrados.</div><?php else: ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Código</th><th>Cliente</th><th>Data</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach ($ultimos as $p): ?><tr><td><strong><?= h($p['codigo']) ?></strong></td><td><?= h($p['cliente']) ?></td><td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td><td><?= money($p['total']) ?></td><td><span class="status-pill status-<?= h(strtolower(str_replace(' ', '-', $p['status']))) ?>"><?= h($p['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
