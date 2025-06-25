<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/sanitize.php';

$db = new Database();
$conn = $db->connect();

// Buscar produtos em destaque (os mais recentes)
$stmt = $conn->prepare("
    SELECT p.*, u.nome as vendedor_nome 
    FROM produtos p 
    JOIN usuarios u ON p.vendedor_id = u.id 
    WHERE p.ativo = 1 
    ORDER BY p.data_cadastro DESC 
    LIMIT 8
");
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para exibição
$categories = $conn->query("
    SELECT categoria, COUNT(*) as total 
    FROM produtos 
    WHERE ativo = 1 AND categoria IS NOT NULL 
    GROUP BY categoria 
    ORDER BY total DESC 
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cakee Market - Deliciosos Bolos Artesanais</title>
    <meta name="description" content="Compre os melhores bolos artesanais diretamente dos confeiteiros. Variedade de sabores e opções para todos os gostos.">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- Hero Banner -->
        <section class="hero">
            <div class="hero-content">
                <h1>Bolos Artesanais Feitos com Amor</h1>
                <p>Descubra os melhores bolos caseiros diretamente dos confeiteiros locais</p>
                <a href="/pages/products.php" class="btn btn-hero">Compre Agora</a>
            </div>
        </section>

        <!-- Destaques -->
        <section class="featured-section">
            <div class="container">
                <h2 class="section-title">Nossos Destaques</h2>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <a href="/pages/product_detail.php?id=<?= $product['id'] ?>">
                                <div class="product-image">
                                    <img src="/assets/images/uploads/products/<?= htmlspecialchars($product['imagem_principal']) ?>" alt="<?= htmlspecialchars($product['nome']) ?>">
                                    <?php if ($product['estoque'] <= 0): ?>
                                        <span class="out-of-stock">Esgotado</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?= htmlspecialchars($product['nome']) ?></h3>
                                    <p class="price">R$ <?= number_format($product['preco'], 2, ',', '.') ?></p>
                                    <p class="seller">Por <?= htmlspecialchars($product['vendedor_nome']) ?></p>
                                </div>
                            </a>
                            <button class="add-to-cart" data-id="<?= $product['id'] ?>" <?= $product['estoque'] <= 0 ? 'disabled' : '' ?>>
                                <?= $product['estoque'] <= 0 ? 'Esgotado' : 'Adicionar ao Carrinho' ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="/pages/products.php" class="btn">Ver Todos os Produtos</a>
                </div>
            </div>
        </section>

        <!-- Categorias -->
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title">Explore por Categorias</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <a href="/pages/products.php?category=<?= urlencode($category['categoria']) ?>" class="category-card">
                            <div class="category-icon">
                                <i class="fas fa-birthday-cake"></i>
                            </div>
                            <h3><?= htmlspecialchars($category['categoria']) ?></h3>
                            <p><?= $category['total'] ?> produtos</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Sobre Nós -->
        <section class="about-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-text">
                        <h2 class="section-title">Sobre o Cakee Market</h2>
                        <p>O Cakee Market conecta você aos melhores confeiteiros da sua região, oferecendo bolos artesanais feitos com ingredientes de qualidade e muito carinho.</p>
                        <p>Nossa missão é valorizar o trabalho dos pequenos produtores e levar até você uma experiência gastronômica única.</p>
                        <div class="about-features">
                            <div class="feature">
                                <i class="fas fa-star"></i>
                                <span>Produtos selecionados</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-truck"></i>
                                <span>Entrega rápida</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-heart"></i>
                                <span>Feito com amor</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Depoimentos -->
        <section class="testimonials-section">
            <div class="container">
                <h2 class="section-title">O que nossos clientes dizem</h2>
                <div class="testimonials">
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <p>"Os bolos são incríveis! Sempre fresquinhos e deliciosos. Minha família adorou!"</p>
                            <div class="testimonial-author">
                                <img src="/assets/images/testimonials/user1.jpg" alt="Ana Silva">
                                <div>
                                    <h4>Ana Silva</h4>
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <p>"Adorei a variedade de opções. Encontrei exatamente o bolo que queria para o aniversário da minha filha."</p>
                            <div class="testimonial-author">
                                <img src="/assets/images/testimonials/user2.jpg" alt="Carlos Mendes">
                                <div>
                                    <h4>Carlos Mendes</h4>
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <p>"Como confeiteira, achei incrível a plataforma. Me ajudou a alcançar mais clientes!"</p>
                            <div class="testimonial-author">
                                <img src="/assets/images/testimonials/user3.jpg" alt="Mariana Costa">
                                <div>
                                    <h4>Mariana Costa</h4>
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <div class="container">
                <h2>Pronto para experimentar?</h2>
                <p>Descubra os melhores bolos artesanais da sua região</p>
                <div class="cta-buttons">
                    <a href="/pages/products.php" class="btn">Compre Agora</a>
                    <a href="/pages/auth/register.php" class="btn btn-outline">Cadastre-se como Vendedor</a>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="/assets/js/main.js"></script>
    <script>
        // Adicionar ao carrinho
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('/includes/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Atualizar contador do carrinho
                        const cartCount = document.querySelector('.cart-count');
                        if(cartCount) {
                            cartCount.textContent = data.cart_count;
                            cartCount.style.display = 'flex';
                        } else {
                            // Criar contador se não existir
                            const cartIcon = document.querySelector('.cart-icon');
                            if(cartIcon) {
                                const count = document.createElement('span');
                                count.className = 'cart-count';
                                count.textContent = data.cart_count;
                                cartIcon.appendChild(count);
                            }
                        }
                        
                        // Feedback visual
                        this.textContent = 'Adicionado!';
                        this.style.backgroundColor = '#28a745';
                        setTimeout(() => {
                            this.textContent = 'Adicionar ao Carrinho';
                            this.style.backgroundColor = '';
                        }, 2000);
                    } else {
                        alert(data.message || 'Erro ao adicionar ao carrinho');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao adicionar ao carrinho');
                });
            });
        });
    </script>
</body>
</html>