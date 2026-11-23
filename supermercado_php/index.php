<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = trim($_GET['busca'] ?? '');
$category = trim($_GET['categoria'] ?? '');
$sql = "SELECT * FROM produtos WHERE ativo = 1";
$params = [];
if ($search !== '') {
    $sql .= " AND (nome LIKE :busca OR descricao LIKE :busca OR categoria LIKE :busca)";
    $params['busca'] = '%' . $search . '%';
}
if ($category !== '') {
    $sql .= " AND categoria = :categoria";
    $params['categoria'] = $category;
}
$sql .= " ORDER BY destaque DESC, nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();
$categorias = $pdo->query("SELECT DISTINCT categoria FROM produtos WHERE ativo = 1 ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Sua compra começa aqui';
$activePage = 'loja';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow"><span class="eyebrow-dot"></span> MAIS FRESCOR, MAIS ECONOMIA</span>
        <h1>O melhor do mercado, <em>pertinho de você.</em></h1>
        <p>Encontre seus produtos favoritos, monte seu carrinho e deixe o resto com a gente.</p>
        <a class="button" href="#produtos">Explorar produtos <span>→</span></a>
        <div class="hero-perks"><span>✓ Compra fácil</span><span>✓ Preços especiais</span><span>✓ Pedido organizado</span></div>
    </div>
    <div class="hero-art" aria-label="Ilustração de compras de supermercado">
        <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
        <div class="hero-leaf leaf-one">✦</div><div class="hero-leaf leaf-two">✦</div>
        <div class="basket-illustration"><div class="basket-handle"></div><div class="basket-body"><span>🥑</span><span>🥦</span><span>🥕</span><span>🍋</span><span>🍅</span></div></div>
        <div class="floating-note note-top"><span>🌱</span><div><b>Fresquinho</b><small>todos os dias</small></div></div>
        <div class="floating-note note-bottom"><span>🛒</span><div><b>Seu carrinho</b><small>do seu jeito</small></div></div>
        <div class="hero-blob"></div>
    </div>
</section>

<section class="promo-strip">
    <div class="promo-icon">%</div>
    <div><strong>Economize na sua compra!</strong><span>Ganhe 5% de desconto em compras acima de R$ 200,00.</span></div>
    <span class="promo-arrow">→</span>
</section>

<section class="products-section" id="produtos">
    <div class="section-heading">
        <div><span class="eyebrow">ESCOLHAS DO DIA</span><h2>Encontre seus favoritos</h2><p>Produtos selecionados para deixar sua rotina mais leve.</p></div>
        <form class="search-form" method="get" action="<?= h(url('index.php')) ?>">
            <?php if ($category !== ''): ?><input type="hidden" name="categoria" value="<?= h($category) ?>"><?php endif; ?>
            <span>⌕</span><input type="search" name="busca" placeholder="Buscar produtos..." value="<?= h($search) ?>"><button type="submit" aria-label="Buscar">→</button>
        </form>
    </div>
    <div class="category-tabs">
        <a class="category-tab <?= $category === '' ? 'selected' : '' ?>" href="<?= h(url('index.php')) ?>">Todos</a>
        <?php foreach ($categorias as $cat): ?><a class="category-tab <?= $category === $cat ? 'selected' : '' ?>" href="<?= h(url('index.php?categoria=' . urlencode($cat))) ?>"><?= h($cat) ?></a><?php endforeach; ?>
    </div>
    <?php if (!$produtos): ?>
        <div class="empty-state"><div>🥬</div><h3>Nenhum produto encontrado</h3><p>Tente buscar por outro nome ou categoria.</p><a class="button button-outline" href="<?= h(url('index.php')) ?>">Ver todos os produtos</a></div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($produtos as $produto): ?>
                <article class="product-card">
                    <div class="product-visual <?= $produto['destaque'] ? 'product-featured' : '' ?>">
                        <?php if ($produto['destaque']): ?><span class="product-badge">Destaque</span><?php endif; ?>
                        <span class="product-emoji"><?= h(productIcon($produto['icone'])) ?></span>
                        <span class="product-stock"><?= (int) $produto['estoque'] > 0 ? 'Disponível' : 'Esgotado' ?></span>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= h($produto['categoria']) ?></span>
                        <h3><?= h($produto['nome']) ?></h3>
                        <p><?= h($produto['descricao'] ?: 'Qualidade e praticidade para sua casa.') ?></p>
                        <div class="product-bottom"><div><strong><?= money($produto['preco']) ?></strong><small>/ <?= h($produto['unidade']) ?></small></div>
                            <?php if ($produto['estoque'] > 0): ?>
                                <?php if (currentUser() && currentUser()['tipo'] === 'cliente'): ?>
                                    <form method="post" action="<?= h(url('carrinho.php')) ?>" class="add-form"><input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>"><button class="add-button" type="submit" aria-label="Adicionar <?= h($produto['nome']) ?>">+</button></form>
                                <?php elseif (!currentUser()): ?>
                                    <a class="add-button add-link" href="<?= h(url('login.php')) ?>" aria-label="Entre para adicionar">+</a>
                                <?php else: ?><span class="admin-small-note">Admin</span><?php endif; ?>
                            <?php else: ?><button class="add-button" disabled>−</button><?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<section class="bottom-callout"><div class="callout-icon">🥑</div><div><h2>Uma compra mais simples começa aqui.</h2><p>Adicione seus produtos ao carrinho e acompanhe seu pedido em poucos cliques.</p></div><a href="#produtos" class="button button-white">Começar agora <span>→</span></a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
