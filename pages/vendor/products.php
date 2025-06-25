<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/vendor_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/sanitize.php';
require_once __DIR__ . '/../../functions/upload.php';

$db = new Database();
$conn = $db->connect();

$error = '';
$success = '';

// Adicionar novo produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $category = sanitize($_POST['category']);
    $ingredients = sanitize($_POST['ingredients']);
    $weight = (float)$_POST['weight'];
    $prep_time = (int)$_POST['prep_time'];
    $stock = (int)$_POST['stock'];
    
    // Validações
    if (empty($name) || empty($price)) {
        $error = 'Name and price are required';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } else {
        // Upload da imagem principal
        $main_image = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['main_image'], 'products');
            if ($upload['success']) {
                $main_image = $upload['file_name'];
            } else {
                $error = $upload['error'];
            }
        } else {
            $error = 'Main image is required';
        }
        
        // Upload de imagens adicionais
        $additional_images = [];
        if (empty($error) && !empty($_FILES['additional_images']['name'][0])) {
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['additional_images']['name'][$key],
                        'type' => $_FILES['additional_images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['additional_images']['error'][$key],
                        'size' => $_FILES['additional_images']['size'][$key]
                    ];
                    
                    $upload = uploadImage($file, 'products');
                    if ($upload['success']) {
                        $additional_images[] = $upload['file_name'];
                    }
                }
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $this->conn->prepare("
                    INSERT INTO produtos 
                    (vendedor_id, nome, descricao, preco, categoria, imagem_principal, imagens_adicionais, estoque, ingredientes, peso, tempo_preparo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $name,
                    $description,
                    $price,
                    $category,
                    $main_image,
                    json_encode($additional_images),
                    $stock,
                    $ingredients,
                    $weight,
                    $prep_time
                ]);
                
                $success = 'Product added successfully!';
            } catch (PDOException $e) {
                error_log("Product Add Error: " . $e->getMessage());
                $error = 'Failed to add product. Please try again.';
            }
        }
    }
}

// Buscar produtos do vendedor
$stmt = $conn->prepare("
    SELECT * FROM produtos 
    WHERE vendedor_id = ? 
    ORDER BY data_cadastro DESC
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Cakee Market</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="vendor-dashboard">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="/pages/vendor/dashboard.php">Dashboard</a></li>
                    <li class="active"><a href="/pages/vendor/products.php">My Products</a></li>
                    <li><a href="/pages/vendor/orders.php">Orders</a></li>
                    <li><a href="/pages/user/profile.php">Profile</a></li>
                    <li><a href="/pages/auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <h1>My Products</h1>
            
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <button id="toggle-form" class="btn">Add New Product</button>
            
            <div id="product-form" style="display: none;">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category">
                    </div>
                    
                    <div class="form-group">
                        <label for="ingredients">Ingredients</label>
                        <textarea id="ingredients" name="ingredients" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weight">Weight (g)</label>
                            <input type="number" id="weight" name="weight" step="0.1" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="prep_time">Preparation Time (minutes)</label>
                            <input type="number" id="prep_time" name="prep_time" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" min="0" value="1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="main_image">Main Image (required)</label>
                        <input type="file" id="main_image" name="main_image" accept="image/*" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_images">Additional Images (optional)</label>
                        <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>
            
            <div class="products-list">
                <?php if (empty($products)): ?>
                    <p>You haven't added any products yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="/assets/images/uploads/products/<?= htmlspecialchars($product['imagem_principal']) ?>" alt="<?= htmlspecialchars($product['nome']) ?>" width="50">
                                    </td>
                                    <td><?= htmlspecialchars($product['nome']) ?></td>
                                    <td>$<?= number_format($product['preco'], 2) ?></td>
                                    <td><?= $product['estoque'] ?></td>
                                    <td><?= $product['ativo'] ? 'Active' : 'Inactive' ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn">Edit</a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <button type="submit" name="toggle_status" class="btn">
                                                <?= $product['ativo'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        document.getElementById('toggle-form').addEventListener('click', function() {
            const form = document.getElementById('product-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            this.textContent = form.style.display === 'none' ? 'Add New Product' : 'Hide Form';
        });
    </script>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>