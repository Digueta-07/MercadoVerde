<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$rootPath = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__));
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)) ?: ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)));
$basePath = '';
if (str_starts_with(strtolower($rootPath), strtolower($documentRoot))) {
    $basePath = substr($rootPath, strlen($documentRoot));
}
define('BASE_URL', rtrim($basePath, '/'));

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function currentUser(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        setFlash('Faça login para continuar.', 'warning');
        redirect('login.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if ((currentUser()['tipo'] ?? '') !== 'admin') {
        setFlash('Acesso permitido somente ao administrador.', 'danger');
        redirect('index.php');
    }
}

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Sua sessão expirou. Volte e tente novamente.');
    }
}

function cartCount(): int
{
    return array_sum($_SESSION['carrinho'] ?? []);
}

function productIcon(string $icon): string
{
    return $icon !== '' ? $icon : '🛒';
}
