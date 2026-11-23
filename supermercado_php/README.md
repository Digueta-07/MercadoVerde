# Mercado Verde — Sistema de Supermercado

Projeto acadêmico simples de supermercado desenvolvido em **PHP + MySQL**, com interface responsiva inspirada nas referências enviadas.

## Funcionalidades

- Cadastro e login de clientes.
- Login de administrador.
- Catálogo de produtos com busca e filtro por categoria.
- Carrinho com alteração de quantidades, remoção de itens e cálculo automático.
- Finalização de compra simulada, sem gateway de pagamento.
- Geração de código único para cada pedido, associado ao usuário logado.
- Consulta dos pedidos pelo cliente.
- Painel administrativo com indicadores.
- CRUD de produtos (cadastrar, listar, editar e remover da loja).
- Controle de estoque com baixa automática na finalização do pedido.
- Devolução ao estoque quando o administrador cancela um pedido.
- Desconto automático de **5% quando o subtotal é superior a R$ 200,00**.
- Senhas armazenadas com `password_hash()` e verificadas com `password_verify()`.

## Requisitos

- XAMPP (Apache + MySQL + PHP 8.0 ou superior recomendado).
- Navegador atualizado.

## Como instalar no XAMPP

1. Extraia a pasta `supermercado_php` dentro de `C:\xampp\htdocs\`.
2. Abra o XAMPP Control Panel e inicie **Apache** e **MySQL**.
3. Acesse `http://localhost/phpmyadmin`.
4. Na aba **Importar**, selecione o arquivo `supermercado_php/database/supermercado.sql` e execute a importação. O script cria o banco `supermercado_db`, as tabelas e alguns produtos de exemplo.
5. Confira as configurações em `supermercado_php/config/database.php`. O padrão é host `localhost`, banco `supermercado_db`, usuário `root` e senha vazia (padrão comum do XAMPP).
6. Acesse `http://localhost/supermercado_php/`.

> Se você renomear a pasta, acesse o endereço com o novo nome. O sistema identifica automaticamente o caminho da aplicação dentro do `htdocs`.

## Acessos iniciais

**Administrador**

- E-mail: `admin@mercado.com`
- Senha: `admin123`

Após entrar, recomenda-se alterar a senha do administrador diretamente no banco de dados ou implementar uma tela de alteração de senha antes de publicar o sistema.

**Cliente:** crie uma conta pela opção “Criar conta” na página inicial.

## Fluxo de compra

1. O cliente cria uma conta e entra no sistema.
2. Escolhe os produtos e adiciona ao carrinho.
3. O sistema soma os preços multiplicados pelas quantidades.
4. Quando o subtotal ultrapassa R$ 200,00, aplica 5% de desconto.
5. Ao confirmar, o sistema valida o estoque e registra o pedido e seus itens em uma transação SQL.
6. O estoque é baixado automaticamente.
7. O pedido recebe um código no formato `MV-AAMMDD-XXXXXX` e fica visível em **Pedidos** no painel administrativo.
8. O administrador atualiza o status conforme separa e conclui a compra.

## Organização das pastas

```text
supermercado_php/
├── admin/                 # Painel, produtos e pedidos
├── assets/
│   ├── css/style.css      # Estilos e responsividade
│   └── js/app.js          # Pequenas interações do carrinho
├── config/database.php    # Conexão MySQL via PDO
├── database/supermercado.sql
├── includes/              # Cabeçalho, rodapé e funções comuns
├── index.php              # Loja / catálogo
├── login.php
├── registrar.php
├── carrinho.php
├── pedidos.php
└── logout.php
```

## Observações acadêmicas

- A compra é uma **simulação**: não existe integração com pagamento, entrega ou emissão fiscal.
- O administrador é criado pelo SQL de instalação. O cadastro público cria somente usuários do tipo `cliente`.
- A exclusão de produto é lógica (`ativo = 0`) para preservar o histórico dos pedidos.
- O estoque é validado e atualizado dentro de uma transação do banco para evitar registrar pedidos com quantidades indisponíveis.
- Este projeto é uma base didática. Antes de usar em produção, adicione políticas de backup, recuperação de senha, auditoria, permissões mais detalhadas e ajustes de segurança/servidor.
