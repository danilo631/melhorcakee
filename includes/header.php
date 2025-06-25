<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->connect();

// Verificar se o usuário está logado e obter informações
$usuario = null;
$carrinho_count = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id, nome, email, foto_perfil, tipo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Contar itens no carrinho
    $stmt = $conn->prepare("SELECT COUNT(*) FROM carrinho WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $carrinho_count = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cakee Market - <?= isset($page_title) ? htmlspecialchars($page_title) : 'Deliciosos Bolos Artesanais' ?></title>
    <meta name="description" content="Compre os melhores bolos artesanais diretamente dos confeiteiros. Variedade de sabores e opções para todos os gostos.">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- JavaScript -->
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <!-- Menu Mobile Hamburger -->
            <button class="mobile-menu-btn" aria-label="Abrir menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Logo -->
            <div class="logo">
                <a href="/">
                    <img src="/assets/images/logo/logo.png" alt="Cakee Market" width="150">
                </a>
            </div>
            
            <!-- Menu de Navegação Principal -->
            <nav class="main-nav">
                <ul>
                    <li><a href="/" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="/pages/products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">Produtos</a></li>
                    <li><a href="/pages/about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">Sobre Nós</a></li>
                    <li><a href="/pages/contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contato</a></li>
                </ul>
            </nav>
            
            <!-- Ícones de Usuário/Carrinho -->
            <div class="header-icons">
                <?php if ($usuario): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <?php if ($usuario['foto_perfil']): ?>
                                <img src="/assets/images/uploads/profiles/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="<?= htmlspecialchars($usuario['nome']) ?>" class="user-avatar">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                            <span class="user-name"><?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="/pages/user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <a href="/pages/user/profile.php"><i class="fas fa-user"></i> Meu Perfil</a>
                            <a href="/pages/user/orders.php"><i class="fas fa-box-open"></i> Meus Pedidos</a>
                            <?php if ($usuario['tipo'] == 'vendedor' || $usuario['tipo'] == 'admin'): ?>
                                <a href="/pages/vendor/dashboard.php"><i class="fas fa-store"></i> Painel do Vendedor</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="/pages/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/pages/auth/login.php" class="auth-link"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                    <a href="/pages/auth/register.php" class="auth-link"><i class="fas fa-user-plus"></i> Cadastrar</a>
                <?php endif; ?>
                
                <a href="/pages/cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($carrinho_count > 0): ?>
                        <span class="cart-count"><?= $carrinho_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <!-- Menu Mobile -->
        <nav class="mobile-nav">
            <div class="mobile-nav-header">
                <button class="mobile-close-btn" aria-label="Fechar menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <ul>
                <li><a href="/" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="/pages/products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">Produtos</a></li>
                <li><a href="/pages/about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">Sobre Nós</a></li>
                <li><a href="/pages/contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contato</a></li>
                
                <?php if ($usuario): ?>
                    <li class="mobile-user-info">
                        <?php if ($usuario['foto_perfil']): ?>
                            <img src="/assets/images/uploads/profiles/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="<?= htmlspecialchars($usuario['nome']) ?>" class="mobile-user-avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($usuario['nome']) ?></span>
                    </li>
                    <li><a href="/pages/user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="/pages/user/profile.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                    <li><a href="/pages/user/orders.php"><i class="fas fa-box-open"></i> Meus Pedidos</a></li>
                    <?php if ($usuario['tipo'] == 'vendedor' || $usuario['tipo'] == 'admin'): ?>
                        <li><a href="/pages/vendor/dashboard.php"><i class="fas fa-store"></i> Painel do Vendedor</a></li>
                    <?php endif; ?>
                    <li><a href="/pages/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                <?php else: ?>
                    <li><a href="/pages/auth/login.php"><i class="fas fa-sign-in-alt"></i> Entrar</a></li>
                    <li><a href="/pages/auth/register.php"><i class="fas fa-user-plus"></i> Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">