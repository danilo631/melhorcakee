<?php
class Cart {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function addItem($product_id, $quantity = 1) {
        // Verificar se o produto existe e está ativo
        $stmt = $this->conn->prepare("
            SELECT p.*, u.nome as vendedor_nome 
            FROM produtos p 
            JOIN usuarios u ON p.vendedor_id = u.id 
            WHERE p.id = ? AND p.ativo = 1
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return false;
        }
        
        // Adicionar ou atualizar item no carrinho
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        return true;
    }
    
    public function updateItem($product_id, $quantity) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            return true;
        }
        return false;
    }
    
    public function removeItem($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            return true;
        }
        return false;
    }
    
    public function getItems() {
        $items = [];
        
        if (!empty($_SESSION['cart'])) {
            $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
            $product_ids = array_keys($_SESSION['cart']);
            
            $stmt = $this->conn->prepare("
                SELECT p.*, u.nome as vendedor_nome 
                FROM produtos p 
                JOIN usuarios u ON p.vendedor_id = u.id 
                WHERE p.id IN ($placeholders)
            ");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $items[] = [
                    'id' => $product['id'],
                    'nome' => $product['nome'],
                    'preco' => $product['preco'],
                    'imagem_principal' => $product['imagem_principal'],
                    'vendedor_nome' => $product['vendedor_nome'],
                    'quantity' => $_SESSION['cart'][$product['id']]
                ];
            }
        }
        
        return $items;
    }
    
    public function getSubtotal() {
        $subtotal = 0;
        $items = $this->getItems();
        
        foreach ($items as $item) {
            $subtotal += $item['preco'] * $item['quantity'];
        }
        
        return $subtotal;
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
    }
    
    public function countItems() {
        return array_sum($_SESSION['cart']);
    }
}
?>