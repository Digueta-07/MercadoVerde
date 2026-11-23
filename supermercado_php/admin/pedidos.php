<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
    $novoStatus = $_POST['status'] ?? '';
    $permitidos = ['Pendente', 'Em separação', 'Concluído', 'Cancelado'];
    if (!in_array($novoStatus, $permitidos, true)) {
        setFlash('Status inválido.', 'danger'); redirect('admin/pedidos.php');
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT status FROM pedidos WHERE id = ? FOR UPDATE');
        $stmt->execute([$pedidoId]);
        $pedidoAtual = $stmt->fetch();
        if (!$pedidoAtual) throw new RuntimeException('Pedido não encontrado.');
        if ($pedidoAtual['status'] !== 'Cancelado' && $novoStatus === 'Cancelado') {
            $it = $pdo->prepare('SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = ? AND produto_id IS NOT NULL');
            $it->execute([$pedidoId]);
            $restaurar = $pdo->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?');
            foreach ($it->fetchAll() as $item) $restaurar->execute([$item['quantidade'], $item['produto_id']]);
        }
        if ($pedidoAtual['status'] === 'Cancelado' && $novoStatus !== 'Cancelado') {
            throw new RuntimeException('Um pedido cancelado não pode ser reaberto.');
        }
        $update = $pdo->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
        $update->execute([$novoStatus, $pedidoId]);
        $pdo->commit();
        setFlash('Status do pedido atualizado.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setFlash($e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível atualizar o pedido.', 'danger');
    }
    redirect('admin/pedidos.php');
}
$pedidos = $pdo->query('SELECT p.*, u.nome AS cliente, u.email FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.criado_em DESC')->fetchAll();
$pageTitle = 'Pedidos recebidos';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CENTRAL DE COMPRAS</span><h1>Pedidos recebidos</h1><p>Consulte os códigos e separe os produtos de cada cliente.</p></div><a href="<?= h(url('admin/index.php')) ?>" class="text-link">← Voltar ao painel</a></div>
<div class="admin-nav"><a href="<?= h(url('admin/index.php')) ?>">Visão geral</a><a href="<?= h(url('admin/produtos.php')) ?>">Produtos e estoque</a><a class="admin-nav-active" href="<?= h(url('admin/pedidos.php')) ?>">Pedidos</a></div>
<?php if (!$pedidos): ?><div class="empty-state"><div>📋</div><h3>Nenhum pedido por enquanto</h3><p>Quando um cliente finalizar uma compra, os detalhes aparecerão aqui.</p></div><?php else: ?><div class="admin-orders-list">
<?php foreach ($pedidos as $pedido): ?><article class="admin-order-card"><div class="admin-order-top"><div><span class="eyebrow">CÓDIGO DO PEDIDO</span><h2><?= h($pedido['codigo']) ?></h2><p><?= h($pedido['cliente']) ?> · <?= h($pedido['email']) ?> · <?= date('d/m/Y H:i', strtotime($pedido['criado_em'])) ?></p></div><span class="status-pill status-<?= h(strtolower(str_replace(' ', '-', $pedido['status']))) ?>"><?= h($pedido['status']) ?></span></div><div class="admin-order-content"><div class="order-items admin-order-items"><?php $stmt = $pdo->prepare('SELECT nome_produto, quantidade, preco_unitario, total_item FROM itens_pedido WHERE pedido_id = ?'); $stmt->execute([$pedido['id']]); foreach ($stmt->fetchAll() as $item): ?><div><span><?= (int) $item['quantidade'] ?>× <?= h($item['nome_produto']) ?> <small>(<?= money($item['preco_unitario']) ?> cada)</small></span><b><?= money($item['total_item']) ?></b></div><?php endforeach; ?></div><div class="admin-order-summary"><span>Subtotal: <?= money($pedido['subtotal']) ?></span><span>Desconto: <?= money($pedido['desconto']) ?></span><strong><?= money($pedido['total']) ?></strong></div></div><div class="admin-order-bottom"><small>Itens para separação: confira as quantidades acima.</small><?php if ($pedido['status'] !== 'Cancelado'): ?><form method="post" class="status-form"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>"><select name="status"><option <?= $pedido['status'] === 'Pendente' ? 'selected' : '' ?>>Pendente</option><option <?= $pedido['status'] === 'Em separação' ? 'selected' : '' ?>>Em separação</option><option <?= $pedido['status'] === 'Concluído' ? 'selected' : '' ?>>Concluído</option><option>Cancelado</option></select><button class="button button-small" type="submit">Atualizar status</button></form><?php else: ?><span class="cancelled-note">Pedido cancelado · estoque devolvido</span><?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
