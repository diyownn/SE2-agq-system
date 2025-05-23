<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$key = $_ENV['ENCRYPTION_KEY'];


if (!$key && $role == '') {
    header("Location: UNAUTHORIZED.php?error=401k");
    exit();
}

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

function encrypt_url($url, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted_url = openssl_encrypt($url, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted_url . '::' . $iv);
}

function decrypt_url($encrypted_url, $key)
{
    list($encrypted_url, $iv) = explode('::', base64_decode($encrypted_url), 2);
    return openssl_decrypt($encrypted_url, 'aes-256-cbc', $key, 0, $iv);
}


$original_url = 'http://localhost/SE2-agq-system/AGQ/agq_archive.php';
$encrypted_url = encrypt_url($original_url, $key);
$encoded_url = urlencode($encrypted_url);

header('Location: agq_archive.php?url=' . $encoded_url);
exit;
?>