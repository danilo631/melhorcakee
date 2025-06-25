<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/sanitize.php';
require_once __DIR__ . '/../../functions/upload.php';

$db = new Database();
$conn = $db->connect();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $user_type = sanitize($_POST['user_type']);
    
    // Validações
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email and password are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            // Verificar se email já existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email already registered';
            } else {
                // Upload da foto de perfil
                $profile_pic = '';
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadImage($_FILES['profile_pic'], 'profiles');
                    if ($upload['success']) {
                        $profile_pic = $upload['file_name'];
                    } else {
                        $error = $upload['error'];
                    }
                }
                
                if (empty($error)) {
                    // Hash da senha
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Inserir usuário
                    $stmt = $conn->prepare("
                        INSERT INTO usuarios 
                        (nome, email, senha, telefone, endereco, foto_perfil, tipo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $name, $email, $hashed_password, $phone, $address, $profile_pic, $user_type
                    ]);
                    
                    $success = 'Registration successful! You can now login.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cakee Market</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Criar Conta</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefone</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="address">Endereço</label>
                <textarea id="address" name="address"></textarea>
            </div>
            
            <div class="form-group">
                <label for="profile_pic">Foto de Perfil</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="user_type">Tipo de Conta</label>
                <select id="user_type" name="user_type" required>
                    <option value="cliente">Cliente</option>
                    <option value="vendedor">Vendedor</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Registrar</button>
        </form>
        
        <p>Já tem uma conta? <a href="/pages/auth/login.php">Faça login</a></p>
    </div>
</body>
</html>