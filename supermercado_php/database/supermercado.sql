CREATE DATABASE IF NOT EXISTS supermercado_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE supermercado_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    categoria VARCHAR(80) NOT NULL,
    unidade VARCHAR(30) NOT NULL DEFAULT 'unidade',
    icone VARCHAR(20) NOT NULL DEFAULT '🛒',
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_produtos_categoria (categoria),
    INDEX idx_produtos_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    usuario_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Pendente', 'Em separação', 'Concluído', 'Cancelado') NOT NULL DEFAULT 'Pendente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_pedidos_data (criado_em),
    INDEX idx_pedidos_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NULL,
    nome_produto VARCHAR(120) NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    total_item DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_itens_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    INDEX idx_itens_pedido (pedido_id)
) ENGINE=InnoDB;

-- administrador: admin@mercado.com / admin123

INSERT INTO usuarios (nome, email, senha, tipo)
VALUES ('Administrador', 'admin@mercado.com', '$2y$12$k/QNSufIU6bWKrRgeg/F2.RdPhV.A3e52Hp7BJ2kRkb4/PUuG4XNO', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO produtos (nome, descricao, categoria, unidade, icone, preco, estoque, estoque_minimo, destaque) VALUES
('Banana Prata', 'Bananas selecionadas e no ponto ideal.', 'Hortifruti', 'kg', '🍌', 6.99, 42, 8, 1),
('Maçã Fuji', 'Maçãs frescas, doces e crocantes.', 'Hortifruti', 'kg', '🍎', 9.90, 35, 8, 1),
('Abacate', 'Abacate fresco para receitas e vitaminas.', 'Hortifruti', 'unidade', '🥑', 5.49, 18, 5, 0),
('Tomate', 'Tomates selecionados para o dia a dia.', 'Hortifruti', 'kg', '🍅', 7.50, 29, 8, 1),
('Brócolis', 'Brócolis frescos e bem selecionados.', 'Hortifruti', 'unidade', '🥦', 4.99, 16, 5, 0),
('Cenoura', 'Cenouras frescas e crocantes.', 'Hortifruti', 'kg', '🥕', 4.79, 31, 8, 0),
('Limão', 'Limões frescos para sucos e receitas.', 'Hortifruti', 'kg', '🍋', 3.99, 25, 7, 0),
('Leite Integral', 'Leite integral UHT, embalagem de 1 litro.', 'Laticínios', 'unidade', '🥛', 5.29, 60, 12, 1),
('Queijo Mussarela', 'Queijo fatiado, ideal para lanches.', 'Laticínios', 'kg', '🧀', 39.90, 12, 4, 0),
('Iogurte Natural', 'Iogurte natural cremoso.', 'Laticínios', 'unidade', '🥣', 4.50, 27, 6, 0),
('Pão de Forma', 'Pão macio para o café da manhã.', 'Padaria', 'pacote', '🍞', 8.49, 22, 6, 1),
('Pão Francês', 'Pão francês fresquinho.', 'Padaria', 'kg', '🥖', 14.90, 15, 5, 0),
('Arroz Tipo 1', 'Arroz branco tipo 1, pacote de 5 kg.', 'Mercearia', 'pacote', '🍚', 27.90, 40, 10, 1),
('Feijão Carioca', 'Feijão carioca, pacote de 1 kg.', 'Mercearia', 'pacote', '🫘', 8.99, 36, 10, 0),
('Macarrão Espaguete', 'Macarrão de sêmola, pacote de 500 g.', 'Mercearia', 'pacote', '🍝', 4.79, 48, 10, 0),
('Azeite de Oliva', 'Azeite de oliva extra virgem, 500 ml.', 'Mercearia', 'unidade', '🫒', 32.90, 14, 4, 0),
('Suco de Laranja', 'Suco de laranja integral, 1 litro.', 'Bebidas', 'unidade', '🧃', 9.90, 20, 6, 0),
('Água Mineral', 'Água mineral sem gás, 1,5 litro.', 'Bebidas', 'unidade', '💧', 3.49, 80, 15, 0),
('Café em Pó', 'Café torrado e moído, pacote de 500 g.', 'Mercearia', 'pacote', '☕', 18.90, 30, 8, 1),
('Chocolate', 'Chocolate ao leite, barra de 90 g.', 'Mercearia', 'unidade', '🍫', 7.90, 24, 6, 0);
