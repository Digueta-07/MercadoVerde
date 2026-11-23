<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'adicionar') {
        $id = (int) ($_POST['produto_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT id, nome, estoque FROM produtos WHERE id = ? AND ativo = 1');
        $stmt->execute([$id]);
        $produto = $stmt->fetch();
        if ($produto && $produto['estoque'] > 0) {
            $atual = (int) ($_SESSION['carrinho'][$id] ?? 0);
            $_SESSION['carrinho'][$id] = min($atual + 1, (int) $produto['estoque']);
            setFlash($produto['nome'] . ' adicionado ao carrinho.');
        } else setFlash('Produto indisponível no momento.', 'warning');
        redirect('carrinho.php');
    }
    if ($acao === 'atualizar') {
        foreach (($_POST['quantidade'] ?? []) as $id => $quantidade) {
            $id = (int) $id; $quantidade = (int) $quantidade;
            $stmt = $pdo->prepare('SELECT estoque FROM produtos WHERE id = ? AND ativo = 1');
            $stmt->execute([$id]); $estoque = $stmt->fetchColumn();
            if (!$estoque || $quantidade <= 0) unset($_SESSION['carrinho'][$id]);
            else $_SESSION['carrinho'][$id] = min($quantidade, (int) $estoque);
        }
        setFlash('Carrinho atualizado.');
        redirect('carrinho.php');
    }
    if ($acao === 'remover') {
        unset($_SESSION['carrinho'][(int) ($_POST['produto_id'] ?? 0)]);
        setFlash('Produto removido do carrinho.');
        redirect('carrinho.php');
    }
    if ($acao === 'finalizar') {
        requireLogin();
        if (currentUser()['tipo'] !== 'cliente') {
            setFlash('Somente clientes podem realizar compras.', 'warning');
            redirect('carrinho.php');
        }
        $cart = $_SESSION['carrinho'];
        if (!$cart) {
            setFlash('Adicione produtos antes de finalizar a compra.', 'warning');
            redirect('carrinho.php');
        }
        try {
            $pdo->beginTransaction();
            $itens = []; $subtotal = 0;
            foreach ($cart as $id => $quantidade) {
                $stmt = $pdo->prepare('SELECT id, nome, preco, estoque, unidade FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE');
                $stmt->execute([(int) $id]);
                $produto = $stmt->fetch();
                if (!$produto || (int) $produto['estoque'] < (int) $quantidade) {
                    throw new RuntimeException('Estoque insuficiente para ' . ($produto['nome'] ?? 'um dos produtos') . '. Atualize seu carrinho.');
                }
                $quantidade = (int) $quantidade;
                $linha = round((float) $produto['preco'] * $quantidade, 2);
                $subtotal += $linha;
                $itens[] = ['produto' => $produto, 'quantidade' => $quantidade, 'preco' => $produto['preco'], 'total' => $linha];
            }
            $subtotal = round($subtotal, 2);
            $desconto = $subtotal > 200 ? round($subtotal * 0.05, 2) : 0;
            $total = round($subtotal - $desconto, 2);
            $codigo = 'MV-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $stmt = $pdo->prepare('INSERT INTO pedidos (codigo, usuario_id, subtotal, desconto, total, status) VALUES (?, ?, ?, ?, ?, "Pendente")');
            $stmt->execute([$codigo, currentUser()['id'], $subtotal, $desconto, $total]);
            $pedidoId = (int) $pdo->lastInsertId();
            $insertItem = $pdo->prepare('INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, quantidade, preco_unitario, total_item) VALUES (?, ?, ?, ?, ?, ?)');
            $baixar = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ?');
            foreach ($itens as $item) {
                $insertItem->execute([$pedidoId, $item['produto']['id'], $item['produto']['nome'], $item['quantidade'], $item['preco'], $item['total']]);
                $baixar->execute([$item['quantidade'], $item['produto']['id']]);
            }
            $pdo->commit();
            $_SESSION['carrinho'] = [];
            redirect('pedidos.php?codigo=' . urlencode($codigo));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash($e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível finalizar seu pedido. Tente novamente.', 'danger');
            redirect('carrinho.php');
        }
    }
}

$cart = $_SESSION['carrinho'];
$produtos = [];
$subtotal = 0;
if ($cart) {
    $ids = array_map('intval', array_keys($cart));
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($marks)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $qtd = (int) $cart[$p['id']];
        $p['quantidade_carrinho'] = $qtd;
        $p['total_linha'] = round((float) $p['preco'] * $qtd, 2);
        $subtotal += $p['total_linha'];
        $produtos[] = $p;
    }
}
$desconto = $subtotal > 200 ? round($subtotal * 0.05, 2) : 0;
$total = $subtotal - $desconto;
$pageTitle = 'Meu carrinho';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">SUA SELEÇÃO</span><h1>Meu carrinho <span class="heading-count"><?= cartCount() ?> itens</span></h1><p>Confira os produtos e as quantidades antes de finalizar.</p></div><a class="text-link" href="<?= h(url('index.php')) ?>">← Continuar comprando</a></div>
<?php if (!$produtos): ?><div class="empty-state cart-empty"><div>🛒</div><h3>Seu carrinho está vazio</h3><p>Que tal escolher alguns produtos fresquinhos?</p><a class="button" href="<?= h(url('index.php')) ?>">Explorar produtos <span>→</span></a></div>
<?php else: ?><div class="cart-layout"><section class="cart-items"><form method="post" action="<?= h(url('carrinho.php')) ?>"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="atualizar"><div class="cart-table-head"><span>Produto</span><span>Quantidade</span><span>Total</span></div>
<?php foreach ($produtos as $p): ?><div class="cart-row"><div class="cart-product"><span class="cart-product-icon"><?= h(productIcon($p['icone'])) ?></span><div><b><?= h($p['nome']) ?></b><small><?= money($p['preco']) ?> / <?= h($p['unidade']) ?></small></div></div><div class="quantity-control"><button type="button" class="quantity-step" data-quantity-step="-1">−</button><input type="number" name="quantidade[<?= (int) $p['id'] ?>]" min="0" max="<?= (int) $p['estoque'] ?>" value="<?= (int) $p['quantidade_carrinho'] ?>"><button type="button" class="quantity-step" data-quantity-step="1">+</button></div><div class="cart-line-total"><b><?= money($p['total_linha']) ?></b><button class="remove-button" type="button" onclick="document.getElementById('remove-<?= (int) $p['id'] ?>').submit()">Remover</button></div></div><?php endforeach; ?>
<button class="button button-outline update-cart" type="submit">Atualizar carrinho</button></form>
<?php foreach ($produtos as $p): ?><form id="remove-<?= (int) $p['id'] ?>" method="post" action="<?= h(url('carrinho.php')) ?>" hidden><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="remover"><input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>"></form><?php endforeach; ?></section>
<aside class="summary-card"><h2>Resumo do pedido</h2><div class="summary-line"><span>Subtotal</span><b><?= money($subtotal) ?></b></div><div class="summary-line discount-line"><span>Desconto</span><b>- <?= money($desconto) ?></b></div><div class="discount-hint <?= $desconto > 0 ? 'unlocked' : '' ?>">🎉 <?= $desconto > 0 ? 'Você ganhou 5% de desconto!' : 'Compras acima de R$ 200 ganham 5% OFF.' ?></div><div class="summary-total"><span>Total</span><strong><?= money($total) ?></strong></div>
<?php if (currentUser() && currentUser()['tipo'] === 'cliente'): ?><form method="post" action="<?= h(url('carrinho.php')) ?>"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="finalizar"><button class="button button-full" type="submit" data-confirm="Confirmar a compra? O estoque será atualizado.">Finalizar pedido <span>→</span></button></form><?php else: ?><a class="button button-full" href="<?= h(url('login.php')) ?>">Entre para finalizar <span>→</span></a><?php endif; ?><p class="secure-note">🔒 Simulação de compra. Nenhum pagamento será realizado.</p></aside></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
