<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
if (currentUser()['tipo'] !== 'cliente') redirect('admin/index.php');
$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY criado_em DESC');
$stmt->execute([currentUser()['id']]);
$pedidos = $stmt->fetchAll();
$pageTitle = 'Meus pedidos';
$activePage = 'pedidos';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">ACOMPANHE POR AQUI</span><h1>Meus pedidos</h1><p>Veja o código, os itens e o andamento das suas compras.</p></div><a class="button button-outline" href="<?= h(url('index.php')) ?>">+ Nova compra</a></div>
<?php if (isset($_GET['codigo'])): ?><div class="order-success"><span>✓</span><div><b>Pedido realizado com sucesso!</b><p>Seu código de identificação é <strong><?= h($_GET['codigo']) ?></strong>. Apresente esse código ao supermercado, se necessário.</p></div></div><?php endif; ?>
<?php if (!$pedidos): ?><div class="empty-state"><div>🧺</div><h3>Você ainda não fez pedidos</h3><p>Quando finalizar sua primeira compra, ela aparecerá aqui.</p><a class="button" href="<?= h(url('index.php')) ?>">Ir para a loja <span>→</span></a></div><?php else: ?><div class="orders-list">
<?php foreach ($pedidos as $pedido): ?><article class="order-card"><div class="order-top"><div><span class="eyebrow">CÓDIGO DO PEDIDO</span><h3><?= h($pedido['codigo']) ?></h3><small><?= date('d/m/Y \à\s H:i', strtotime($pedido['criado_em'])) ?></small></div><span class="status-pill status-<?= h(strtolower(str_replace(' ', '-', $pedido['status']))) ?>"><?= h($pedido['status']) ?></span></div><div class="order-items"><?php $it = $pdo->prepare('SELECT nome_produto, quantidade, preco_unitario, total_item FROM itens_pedido WHERE pedido_id = ?'); $it->execute([$pedido['id']]); foreach ($it->fetchAll() as $item): ?><div><span><?= (int) $item['quantidade'] ?>× <?= h($item['nome_produto']) ?></span><b><?= money($item['total_item']) ?></b></div><?php endforeach; ?></div><div class="order-total"><span>Subtotal: <?= money($pedido['subtotal']) ?> · Desconto: <?= money($pedido['desconto']) ?></span><strong>Total: <?= money($pedido['total']) ?></strong></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
