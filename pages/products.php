<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Função de sanitização
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$db = new Database();
$conn = $db->connect();

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filtros
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Query base
$query = "SELECT p.*, u.nome as vendedor_nome 
          FROM produtos p 
          JOIN usuarios u ON p.vendedor_id = u.id 
          WHERE p.ativo = 1";

$params = [];
$types = '';

// Adicionar filtros
if (!empty($category)) {
    $query .= " AND p.categoria = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

// Contar total de produtos
$count_query = "SELECT COUNT(*) as total FROM ($query) as total_query";
$stmt = $conn->prepare($count_query);

if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $per_page);

// Buscar produtos com paginação
$query .= " ORDER BY p.data_cadastro DESC LIMIT ? OFFSET ?";
// Adiciona os parâmetros de paginação como inteiros
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);

// Vincula os parâmetros com seus tipos
if (!empty($params)) {
    for ($i = 0; $i < count($params); $i++) {
        $paramType = PDO::PARAM_STR;
        if ($i === count($params) - 2 || $i === count($params) - 1) {
            $paramType = PDO::PARAM_INT;
        }
        $stmt->bindValue($i + 1, $params[$i], $paramType);
    }
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para filtro
$categories = $conn->query("SELECT DISTINCT categoria FROM produtos WHERE ativo = 1 AND categoria IS NOT NULL ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Cakee Market</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/products.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="products-container">
        <div class="filters-section">
            <h1>Nossos Produtos</h1>
            
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Pesquisar produtos..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($search) || !empty($category)): ?>
                    <a href="products.php" class="btn-clear">Limpar filtros</a>
                <?php endif; ?>
            </form>
            
            <div class="category-filter">
                <h2>Categorias</h2>
                <ul>
                    <li><a href="products.php" class="<?= empty($category) ? 'active' : '' ?>">Todas as categorias</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="products.php?category=<?= urlencode($cat) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="<?= $category === $cat ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>Nenhum produto encontrado.</p>
                    <a href="products.php" class="btn-primary">Ver todos os produtos</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product_detail.php?id=<?= $product['id'] ?>">
                            <div class="product-image">
                                <img src="../assets/images/uploads/products/<?= htmlspecialchars($product['imagem_principal']) ?>" 
                                     alt="<?= htmlspecialchars($product['nome']) ?>" 
                                     onerror="this.src='../assets/images/default-product.jpg'">
                                <?php if ($product['preco_promocional'] > 0): ?>
                                    <span class="discount-badge">Promoção</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['nome']) ?></h3>
                                <div class="price-container">
                                    <?php if ($product['preco_promocional'] > 0): ?>
                                        <span class="original-price">R$ <?= number_format($product['preco'], 2, ',', '.') ?></span>
                                        <span class="promo-price">R$ <?= number_format($product['preco_promocional'], 2, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="price">R$ <?= number_format($product['preco'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="seller">Vendedor: <?= htmlspecialchars($product['vendedor_nome']) ?></p>
                                <?php if ($product['estoque'] > 0): ?>
                                    <p class="stock">Disponível: <?= $product['estoque'] ?> un.</p>
                                <?php else: ?>
                                    <p class="stock out-of-stock">Esgotado</p>
                                <?php endif; ?>
                            </div>
                        </a>
                        <button class="add-to-cart" data-id="<?= $product['id'] ?>" <?= $product['estoque'] <= 0 ? 'disabled' : '' ?>>
                            <?= $product['estoque'] > 0 ? 'Adicionar ao Carrinho' : 'Indisponível' ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="page-link">
                        &laquo; Anterior
                    </a>
                <?php endif; ?>
                
                <?php 
                // Mostrar até 5 páginas ao redor da atual
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                if ($start > 1) {
                    echo '<a href="?page=1'.(!empty($category) ? '&category='.urlencode($category) : '').(!empty($search) ? '&search='.urlencode($search) : '').'" class="page-link">1</a>';
                    if ($start > 2) echo '<span class="page-dots">...</span>';
                }
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="page-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; 
                
                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) echo '<span class="page-dots">...</span>';
                    echo '<a href="?page='.$total_pages.(!empty($category) ? '&category='.urlencode($category) : '').(!empty($search) ? '&search='.urlencode($search) : '').'" class="page-link">'.$total_pages.'</a>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="page-link">
                        Próxima &raquo;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
        // Adicionar ao carrinho via AJAX
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                if (this.disabled) return;
                
                fetch('../actions/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Produto adicionado ao carrinho!');
                        // Atualizar contador do carrinho no header
                        if (document.getElementById('cart-count')) {
                            document.getElementById('cart-count').textContent = data.cart_count;
                        }
                    } else {
                        alert(data.message || 'Erro ao adicionar ao carrinho');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao conectar com o servidor');
                });
            });
        });
    </script>
</body>
</html>