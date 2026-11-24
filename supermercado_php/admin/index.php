<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$stats = [
    'produtos' => (int) $pdo
        ->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')
        ->fetchColumn(),

    'estoque_baixo' => (int) $pdo
        ->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND estoque <= estoque_minimo')
        ->fetchColumn(),

    'pedidos' => (int) $pdo
        ->query('SELECT COUNT(*) FROM pedidos')
        ->fetchColumn(),

    'vendas' => (float) $pdo
        ->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE status <> 'Cancelado'")
        ->fetchColumn(),
];

$ultimos = $pdo
    ->query(
        'SELECT p.*, u.nome AS cliente
         FROM pedidos p
         JOIN usuarios u ON u.id = p.usuario_id
         ORDER BY p.criado_em DESC
         LIMIT 6'
    )
    ->fetchAll();

$pageTitle = 'Painel administrativo';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-welcome">
    <div>
        <span class="eyebrow">ÁREA DE GESTÃO</span>

        <h1>
            Olá,
            <?= h(explode(' ', currentUser()['nome'])[0]) ?> 👋
        </h1>

        <p>Acompanhe o movimento da sua loja por aqui.</p>
    </div>
</div>

<div class="admin-nav">
    <a
        class="admin-nav-active"
        href="<?= h(url('admin/index.php')) ?>"
    >
        Visão geral
    </a>

    <a href="<?= h(url('admin/produtos.php')) ?>">
        Produtos e estoque
    </a>

    <a href="<?= h(url('admin/pedidos.php')) ?>">
        Pedidos
    </a>

    <a href="<?= h(url('admin/usuarios.php')) ?>">
        Usuários
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon green">🛍️</span>
        <small>Produtos ativos</small>
        <strong><?= $stats['produtos'] ?></strong>
        <span class="stat-caption">cadastrados no catálogo</span>
    </div>

    <div class="stat-card">
        <span class="stat-icon orange">📦</span>
        <small>Estoque baixo</small>
        <strong><?= $stats['estoque_baixo'] ?></strong>
        <span class="stat-caption">produtos precisam de atenção</span>
    </div>

    <div class="stat-card">
        <span class="stat-icon blue">🧾</span>
        <small>Total de pedidos</small>
        <strong><?= $stats['pedidos'] ?></strong>
        <span class="stat-caption">registrados na loja</span>
    </div>

    <div class="stat-card">
        <span class="stat-icon purple">💰</span>
        <small>Vendas</small>
        <strong>
            R$ <?= number_format($stats['vendas'], 2, ',', '.') ?>
        </strong>
        <span class="stat-caption">total confirmado</span>
    </div>
</div>

<section class="admin-section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">MOVIMENTAÇÃO</span>
            <h2>Pedidos recentes</h2>
            <p>Veja as compras que chegaram à loja.</p>
        </div>

        <a class="text-link" href="<?= h(url('admin/pedidos.php')) ?>">
            Ver todos
        </a>
    </div>

    <?php if (!$ultimos): ?>
        <div class="empty-panel">
            Ainda não existem pedidos registrados.
        </div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($ultimos as $pedido): ?>
                        <tr>
                            <td>
                                #<?= h($pedido['id']) ?>
                            </td>

                            <td>
                                <?= h($pedido['cliente']) ?>
                            </td>

                            <td>
                                <?= h(date('d/m/Y H:i', strtotime($pedido['criado_em']))) ?>
                            </td>

                            <td>
                                <span class="status status-<?= h(strtolower($pedido['status'])) ?>">
                                    <?= h($pedido['status']) ?>
                                </span>
                            </td>

                            <td>
                                R$ <?= number_format($pedido['total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
