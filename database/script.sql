-- Criação do banco de dados
DROP DATABASE IF EXISTS cakee_market;
CREATE DATABASE cakee_market;
USE cakee_market;

-- Tabela de usuários (deve ser criada primeiro pois outras tabelas referenciam)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    foto_perfil VARCHAR(255),
    tipo ENUM('cliente', 'vendedor', 'admin') DEFAULT 'cliente',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    token_reset_senha VARCHAR(255),
    token_expira DATETIME,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de produtos (referencia usuarios)
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2),
    categoria VARCHAR(50),
    imagem_principal VARCHAR(255) NOT NULL,
    imagens_adicionais JSON,
    estoque INT DEFAULT 0,
    ingredientes TEXT,
    peso DECIMAL(10,2) COMMENT 'em gramas',
    tempo_preparo INT COMMENT 'em minutos',
    destaque BOOLEAN DEFAULT FALSE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_vendedor (vendedor_id),
    INDEX idx_categoria (categoria),
    INDEX idx_destaque (destaque),
    INDEX idx_preco (preco),
    FULLTEXT INDEX ft_nome_descricao (nome, descricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pedidos (referencia usuarios)
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    taxa_entrega DECIMAL(10,2) DEFAULT 0,
    endereco_entrega TEXT NOT NULL,
    metodo_pagamento ENUM('cartao', 'pix', 'boleto', 'dinheiro'),
    observacoes TEXT,
    codigo_rastreio VARCHAR(100),
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_data (data_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens do pedido (referencia pedidos e produtos)
CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    nome_produto VARCHAR(100) NOT NULL,
    imagem_produto VARCHAR(255),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de carrinho (referencia usuarios e produtos)
CREATE TABLE carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    data_adicionado DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_produto (usuario_id, produto_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de avaliações (referencia usuarios, produtos e pedidos)
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    pedido_id INT,
    nota TINYINT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    data_avaliacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    resposta_vendedor TEXT,
    data_resposta DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
    INDEX idx_produto (produto_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de favoritos (referencia usuarios e produtos)
CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    data_adicionado DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_produto (usuario_id, produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cupons de desconto (referencia usuarios)
CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    desconto DECIMAL(5,2) NOT NULL,
    tipo_desconto ENUM('percentual', 'fixo') DEFAULT 'percentual',
    data_validade DATETIME,
    usos_maximos INT DEFAULT 1,
    usos_atual INT DEFAULT 0,
    valor_minimo DECIMAL(10,2) DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    criado_por INT,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cupons utilizados (referencia cupons, usuarios e pedidos)
CREATE TABLE cupons_utilizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cupom_id INT NOT NULL,
    usuario_id INT NOT NULL,
    pedido_id INT NOT NULL,
    data_uso DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_desconto DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cupom_id) REFERENCES cupons(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_cupom (cupom_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de mensagens de contato
CREATE TABLE mensagens_contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN DEFAULT FALSE,
    resposta TEXT,
    data_resposta DATETIME,
    INDEX idx_email (email),
    INDEX idx_lida (lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de atividades (referencia usuarios)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(50) NOT NULL,
    descricao TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    data_log DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_acao (acao),
    INDEX idx_data (data_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserção de dados iniciais

-- Inserir usuário admin (senha: password)
INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES 
('Administrador', 'admin@cakeemarket.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE),
('Confeiteiro João', 'joao@confeiteiro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor', TRUE),
('Cliente Maria', 'maria@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', TRUE);

-- Inserir produtos
INSERT INTO produtos (vendedor_id, nome, slug, descricao, preco, categoria, imagem_principal, estoque, ingredientes, peso, tempo_preparo, destaque, ativo) VALUES
(2, 'Bolo de Chocolate', 'bolo-de-chocolate', 'Delicioso bolo de chocolate com cobertura cremosa', 89.90, 'Chocolate', 'bolo_chocolate_1.jpg', 10, 'Farinha, açúcar, ovos, chocolate em pó, manteiga, leite', 1000, 60, TRUE, TRUE),
(2, 'Bolo de Morango', 'bolo-de-morango', 'Bolo branco com recheio e cobertura de morango fresco', 99.90, 'Frutas', 'bolo_morango_1.jpg', 8, 'Farinha, açúcar, ovos, manteiga, leite, morangos', 1200, 75, TRUE, TRUE),
(2, 'Bolo Red Velvet', 'bolo-red-velvet', 'Clássico bolo vermelho com cream cheese frosting', 129.90, 'Especiais', 'bolo_redvelvet_1.jpg', 5, 'Farinha, açúcar, ovos, manteiga, corante alimentício, buttermilk', 1500, 90, TRUE, TRUE),
(2, 'Bolo de Cenoura', 'bolo-de-cenoura', 'Bolo de cenoura com cobertura de chocolate', 79.90, 'Tradicionais', 'bolo_cenoura_1.jpg', 12, 'Farinha, açúcar, ovos, óleo, cenoura, chocolate', 1100, 50, FALSE, TRUE),
(2, 'Bolo de Limão', 'bolo-de-limao', 'Bolo de limão com glacê de limão siciliano', 85.90, 'Frutas', 'bolo_limao_1.jpg', 7, 'Farinha, açúcar, ovos, manteiga, limão siciliano', 900, 55, TRUE, TRUE);

-- Inserir cupom de desconto
INSERT INTO cupons (codigo, desconto, tipo_desconto, data_validade, usos_maximos, valor_minimo) VALUES
('PRIMEIRACOMPRA', 15.00, 'percentual', DATE_ADD(NOW(), INTERVAL 30 DAY), 100, 50.00);

-- Criar triggers

-- Atualizar data de atualização do produto
DELIMITER //
CREATE TRIGGER before_produto_update
BEFORE UPDATE ON produtos
FOR EACH ROW
BEGIN
    SET NEW.data_atualizacao = NOW();
END//
DELIMITER ;

-- Registrar log ao adicionar produto
DELIMITER //
CREATE TRIGGER after_produto_insert
AFTER INSERT ON produtos
FOR EACH ROW
BEGIN
    INSERT INTO logs (usuario_id, acao, descricao)
    VALUES (NEW.vendedor_id, 'produto_adicionado', CONCAT('Produto "', NEW.nome, '" adicionado'));
END//
DELIMITER ;

-- Criar views

-- View de produtos em destaque
CREATE VIEW vw_produtos_destaque AS
SELECT p.*, u.nome AS vendedor_nome
FROM produtos p
JOIN usuarios u ON p.vendedor_id = u.id
WHERE p.destaque = TRUE AND p.ativo = TRUE AND u.ativo = TRUE
ORDER BY p.data_cadastro DESC;

-- View de vendas por vendedor
CREATE VIEW vw_vendas_vendedor AS
SELECT 
    u.id AS vendedor_id,
    u.nome AS vendedor,
    COUNT(DISTINCT pd.id) AS total_pedidos,
    SUM(pi.quantidade * pi.preco_unitario) AS total_vendido,
    COUNT(DISTINCT pi.produto_id) AS produtos_diferentes
FROM pedido_itens pi
JOIN produtos p ON pi.produto_id = p.id
JOIN usuarios u ON p.vendedor_id = u.id
JOIN pedidos pd ON pi.pedido_id = pd.id
WHERE pd.status != 'cancelado'
GROUP BY u.id, u.nome;

-- Criar procedures

-- Procedure para atualizar estoque
DELIMITER //
CREATE PROCEDURE atualizar_estoque(IN p_produto_id INT, IN p_quantidade INT)
BEGIN
    DECLARE v_estoque_atual INT;
    
    SELECT estoque INTO v_estoque_atual FROM produtos WHERE id = p_produto_id FOR UPDATE;
    
    IF v_estoque_atual >= p_quantidade THEN
        UPDATE produtos SET estoque = estoque - p_quantidade WHERE id = p_produto_id;
        SELECT TRUE AS success;
    ELSE
        SELECT FALSE AS success;
    END IF;
END//
DELIMITER ;

-- Procedure para calcular valor do carrinho
DELIMITER //
CREATE PROCEDURE calcular_carrinho(IN p_usuario_id INT)
BEGIN
    SELECT 
        SUM(c.quantidade * COALESCE(p.preco_promocional, p.preco)) AS subtotal,
        COUNT(c.id) AS itens
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.id
    WHERE c.usuario_id = p_usuario_id AND p.ativo = TRUE;
END//
DELIMITER ;

-- Procedure para finalizar pedido
DELIMITER //
CREATE PROCEDURE finalizar_pedido(
    IN p_cliente_id INT,
    IN p_endereco_entrega TEXT,
    IN p_metodo_pagamento ENUM('cartao', 'pix', 'boleto', 'dinheiro'),
    IN p_observacoes TEXT,
    IN p_cupom_id INT,
    OUT p_pedido_id INT
)
BEGIN
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_taxa_entrega DECIMAL(10,2);
    DECLARE v_desconto DECIMAL(10,2) DEFAULT 0;
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_cupom_valido BOOLEAN DEFAULT FALSE;
    
    -- Calcular subtotal do carrinho
    SELECT SUM(c.quantidade * COALESCE(p.preco_promocional, p.preco)) INTO v_subtotal
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.id
    WHERE c.usuario_id = p_cliente_id AND p.ativo = TRUE;
    
    -- Definir taxa de entrega (exemplo fixo)
    SET v_taxa_entrega = 15.00;
    
    -- Verificar cupom de desconto
    IF p_cupom_id IS NOT NULL THEN
        SELECT 
            CASE 
                WHEN data_validade >= NOW() AND usos_atual < usos_maximos AND ativo = TRUE THEN TRUE
                ELSE FALSE
            END,
            CASE 
                WHEN tipo_desconto = 'percentual' THEN (v_subtotal * desconto / 100)
                ELSE desconto
            END
        INTO v_cupom_valido, v_desconto
        FROM cupons
        WHERE id = p_cupom_id;
        
        IF v_cupom_valido THEN
            -- Atualizar contador de usos do cupom
            UPDATE cupons SET usos_atual = usos_atual + 1 WHERE id = p_cupom_id;
        ELSE
            SET v_desconto = 0;
        END IF;
    END IF;
    
    -- Calcular total
    SET v_total = v_subtotal + v_taxa_entrega - v_desconto;
    
    -- Criar pedido
    INSERT INTO pedidos (
        cliente_id,
        status,
        total,
        subtotal,
        taxa_entrega,
        endereco_entrega,
        metodo_pagamento,
        observacoes
    ) VALUES (
        p_cliente_id,
        'pendente',
        v_total,
        v_subtotal,
        v_taxa_entrega,
        p_endereco_entrega,
        p_metodo_pagamento,
        p_observacoes
    );
    
    SET p_pedido_id = LAST_INSERT_ID();
    
    -- Mover itens do carrinho para itens do pedido
    INSERT INTO pedido_itens (
        pedido_id,
        produto_id,
        quantidade,
        preco_unitario,
        nome_produto,
        imagem_produto
    )
    SELECT 
        p_pedido_id,
        p.id,
        c.quantidade,
        COALESCE(p.preco_promocional, p.preco),
        p.nome,
        p.imagem_principal
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.id
    WHERE c.usuario_id = p_cliente_id AND p.ativo = TRUE;
    
    -- Atualizar estoque
    UPDATE produtos p
    JOIN carrinho c ON p.id = c.produto_id
    SET p.estoque = p.estoque - c.quantidade
    WHERE c.usuario_id = p_cliente_id;
    
    -- Limpar carrinho
    DELETE FROM carrinho WHERE usuario_id = p_cliente_id;
    
    -- Registrar uso do cupom se aplicável
    IF p_cupom_id IS NOT NULL AND v_cupom_valido THEN
        INSERT INTO cupons_utilizados (
            cupom_id,
            usuario_id,
            pedido_id,
            valor_desconto
        ) VALUES (
            p_cupom_id,
            p_cliente_id,
            p_pedido_id,
            v_desconto
        );
    END IF;
    
    -- Registrar log
    INSERT INTO logs (usuario_id, acao, descricao)
    VALUES (p_cliente_id, 'pedido_criado', CONCAT('Pedido #', p_pedido_id, ' criado'));
END//
DELIMITER ;