<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cart.php';

$db = new Database();
$conn = $db->connect();

$cart = new Cart($conn);

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['atualizar_carrinho'])) {
            foreach ($_POST['quantidades'] as $id => $quantidade) {
                $quantidade = (int)$quantidade;
                if ($quantidade < 1) {
                    throw new Exception("Quantidade não pode ser menor que 1");
                }
                $cart->updateItem($id, $quantidade);
            }
            $_SESSION['sucesso'] = 'Carrinho atualizado com sucesso!';
        } elseif (isset($_POST['remover_item'])) {
            $cart->removeItem($_POST['item_id']);
            $_SESSION['sucesso'] = 'Produto removido do carrinho!';
        }
        
        header('Location: /pages/carrinho.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['erro'] = $e->getMessage();
        header('Location: /pages/carrinho.php');
        exit;
    }
}

$itens_carrinho = $cart->getItems();
$subtotal = $cart->getSubtotal();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - Cakee Market</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="pagina-carrinho">
        <h1>Meu Carrinho</h1>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-sucesso"><?= $_SESSION['sucesso'] ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-erro"><?= $_SESSION['erro'] ?></div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
        
        <?php if (empty($itens_carrinho)): ?>
            <div class="carrinho-vazio">
                <img src="../assets/images/empty-cart.svg" alt="Carrinho vazio">
                <p>Seu carrinho está vazio</p>
                <a href="/pages/products.php" class="btn btn-primario">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <form method="POST" class="form-carrinho">
                <div class="tabela-carrinho-container">
                    <table class="tabela-carrinho">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_carrinho as $item): ?>
                                <tr>
                                    <td class="info-produto">
                                        <img src="../assets/images/uploads/produtos/<?= htmlspecialchars($item['imagem_principal']) ?>" 
                                             alt="<?= htmlspecialchars($item['nome']) ?>"
                                             onerror="this.src='../assets/images/default-product.jpg'">
                                        <div>
                                            <h3><?= htmlspecialchars($item['nome']) ?></h3>
                                            <p>Vendedor: <?= htmlspecialchars($item['vendedor_nome']) ?></p>
                                            <?php if ($item['estoque'] < $item['quantity']): ?>
                                                <p class="estoque-baixo">Apenas <?= $item['estoque'] ?> disponíveis</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="preco">
                                        R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                                        <?php if ($item['preco_promocional'] > 0): ?>
                                            <span class="preco-original">R$ <?= number_format($item['preco_promocional'], 2, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="quantidade">
                                        <input type="number" 
                                               name="quantidades[<?= $item['id'] ?>]" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="<?= $item['estoque'] ?>">
                                    </td>
                                    <td class="subtotal">R$ <?= number_format($item['preco'] * $item['quantity'], 2, ',', '.') ?></td>
                                    <td class="acoes">
                                        <button type="submit" name="remover_item" class="btn btn-perigo">
                                            <i class="fas fa-trash"></i> Remover
                                        </button>
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="acoes-carrinho">
                    <a href="/pages/produtos.php" class="btn btn-secundario">
                        <i class="fas fa-arrow-left"></i> Continuar Comprando
                    </a>
                    <button type="submit" name="atualizar_carrinho" class="btn btn-atualizar">
                        <i class="fas fa-sync-alt"></i> Atualizar Carrinho
                    </button>
                </div>
            </form>
            
            <div class="resumo-pedido">
                <h2>Resumo do Pedido</h2>
                <div class="linha-resumo">
                    <span>Subtotal:</span>
                    <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                <div class="linha-resumo">
                    <span>Frete:</span>
                    <span>Calculado no checkout</span>
                </div>
                <div class="linha-resumo total">
                    <span>Total Estimado:</span>
                    <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                
                <a href="/pages/checkout.php" class="btn btn-primario btn-finalizar">
                    <i class="fas fa-credit-card"></i> Finalizar Compra
                </a>
                
                <?php if (isset($_SESSION['cupom'])): ?>
                    <div class="cupom-aplicado">
                        <p>Cupom aplicado: <strong><?= $_SESSION['cupom']['codigo'] ?></strong></p>
                        <p>Desconto: R$ <?= number_format($_SESSION['cupom']['desconto'], 2, ',', '.') ?></p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="/actions/aplicar_cupom.php" class="form-cupom">
                        <input type="text" name="codigo_cupom" placeholder="Código do cupom">
                        <button type="submit" class="btn btn-cupom">Aplicar Cupom</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
        // Validação de quantidade antes de enviar o formulário
        document.querySelector('.form-carrinho').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[type="number"]');
            let valid = true;
            
            inputs.forEach(input => {
                const max = parseInt(input.getAttribute('max'));
                const value = parseInt(input.value);
                
                if (value < 1) {
                    alert('Quantidade não pode ser menor que 1');
                    valid = false;
                } else if (value > max) {
                    alert(`Quantidade não pode ser maior que ${max} para este produto`);
                    valid = false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>