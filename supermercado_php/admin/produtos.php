<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$erro = '';
$editando = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $unidade = trim($_POST['unidade'] ?? 'unidade');
        $icone = trim($_POST['icone'] ?? '🛒');
        $preco = str_replace(',', '.', $_POST['preco'] ?? '0');
        $estoque = (int) ($_POST['estoque'] ?? 0);
        $estoqueMinimo = (int) ($_POST['estoque_minimo'] ?? 0);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        if ($nome === '' || $categoria === '' || !is_numeric($preco) || (float) $preco <= 0 || $estoque < 0 || $estoqueMinimo < 0) {
            $erro = 'Preencha nome, categoria, preço válido e quantidades não negativas.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE produtos SET nome=?, descricao=?, categoria=?, unidade=?, icone=?, preco=?, estoque=?, estoque_minimo=?, destaque=? WHERE id=?');
                $stmt->execute([$nome, $descricao, $categoria, $unidade, $icone, $preco, $estoque, $estoqueMinimo, $destaque, $id]);
                setFlash('Produto atualizado com sucesso.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO produtos (nome, descricao, categoria, unidade, icone, preco, estoque, estoque_minimo, destaque) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$nome, $descricao, $categoria, $unidade, $icone, $preco, $estoque, $estoqueMinimo, $destaque]);
                setFlash('Produto cadastrado com sucesso.');
            }
            redirect('admin/produtos.php');
        }
    }
    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('Produto removido da loja. Os registros de pedidos antigos foram preservados.');
        redirect('admin/produtos.php');
    }
}
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?'); $stmt->execute([(int) $_GET['editar']]); $editando = $stmt->fetch() ?: null;
}
$produtos = $pdo->query('SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome')->fetchAll();
$pageTitle = 'Produtos e estoque';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CATÁLOGO DA LOJA</span><h1>Produtos e estoque</h1><p>Cadastre, edite e acompanhe a disponibilidade dos produtos.</p></div><a href="<?= h(url('admin/index.php')) ?>" class="text-link">← Voltar ao painel</a></div>
<div class="admin-content-grid"><section class="form-panel"><div class="panel-title"><div><span class="eyebrow"><?= $editando ? 'EDIÇÃO' : 'NOVO ITEM' ?></span><h2><?= $editando ? 'Editar produto' : 'Cadastrar produto' ?></h2></div><span class="panel-title-icon">＋</span></div>
<?php if ($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int) ($editando['id'] ?? 0) ?>"><label>Nome do produto<input name="nome" required maxlength="120" value="<?= h($editando['nome'] ?? $_POST['nome'] ?? '') ?>" placeholder="Ex.: Maçã Fuji"></label><label>Descrição<textarea name="descricao" rows="2" maxlength="255" placeholder="Uma breve descrição"><?= h($editando['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea></label><div class="form-two-col"><label>Categoria<input name="categoria" required value="<?= h($editando['categoria'] ?? $_POST['categoria'] ?? '') ?>" placeholder="Hortifruti"></label><label>Unidade<input name="unidade" value="<?= h($editando['unidade'] ?? $_POST['unidade'] ?? 'unidade') ?>" placeholder="kg, un. ..."></label></div><div class="form-two-col"><label>Preço (R$)<input type="number" name="preco" min="0.01" step="0.01" required value="<?= h($editando['preco'] ?? $_POST['preco'] ?? '') ?>"></label><label>Ícone / emoji<input name="icone" maxlength="12" value="<?= h($editando['icone'] ?? $_POST['icone'] ?? '🛒') ?>"></label></div><div class="form-two-col"><label>Quantidade em estoque<input type="number" name="estoque" min="0" required value="<?= h($editando['estoque'] ?? $_POST['estoque'] ?? 0) ?>"></label><label>Estoque mínimo<input type="number" name="estoque_minimo" min="0" required value="<?= h($editando['estoque_minimo'] ?? $_POST['estoque_minimo'] ?? 5) ?>"></label></div><label class="checkbox-label"><input type="checkbox" name="destaque" <?= !empty($editando['destaque']) ? 'checked' : '' ?>> Destacar na página inicial</label><button class="button button-full" type="submit"><?= $editando ? 'Salvar alterações' : 'Cadastrar produto' ?> <span>→</span></button><?php if ($editando): ?><a class="cancel-edit" href="<?= h(url('admin/produtos.php')) ?>">Cancelar edição</a><?php endif; ?></form></section>
<section class="admin-list-panel"><div class="panel-title"><div><span class="eyebrow">INVENTÁRIO</span><h2>Produtos cadastrados <span class="count-badge"><?= count($produtos) ?></span></h2></div></div><div class="inventory-list"><?php foreach ($produtos as $p): ?><div class="inventory-row"><span class="inventory-icon"><?= h(productIcon($p['icone'])) ?></span><div class="inventory-main"><b><?= h($p['nome']) ?></b><small><?= h($p['categoria']) ?> · <?= money($p['preco']) ?></small><span class="inventory-stock <?= $p['estoque'] <= $p['estoque_minimo'] ? 'low-stock' : '' ?>"><?= (int) $p['estoque'] ?> em estoque <?= $p['estoque'] <= $p['estoque_minimo'] ? '· Repor' : '' ?></span></div><div class="inventory-actions"><a href="<?= h(url('admin/produtos.php?editar=' . $p['id'])) ?>" class="mini-action" title="Editar">✎</a><form method="post" onsubmit="return confirm('Remover este produto da loja?')"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button type="submit" class="mini-action delete-action" title="Remover">×</button></form></div></div><?php endforeach; ?><?php if (!$produtos): ?><div class="empty-panel">Nenhum produto cadastrado ainda.</div><?php endif; ?></div></section></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
