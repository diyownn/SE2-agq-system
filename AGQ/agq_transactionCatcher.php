<?php
session_start();

require __DIR__ . '/secret/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$key = $_ENV['ENCRYPTION_KEY'];

if (!$key) {
    die("Location: UNAUTHORIZED.php?error=401k ");
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


if (
    ($role == 'admin' || $role == 'Admin' || $role == 'owner' || $role == 'Owner') &&
    (!isset($pword) || $pword != 'AGQ@2006')
) {
    $original_url = 'http://localhost/SOFT%20ENG/owndash.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ownTransactionView.php?url=' . $encoded_url);
    exit;
} elseif (
    ($role == 'Export Forwarding' || $role == 'Import Forwarding' || $role == 'Export Brokerage' || $role == 'Import Brokerage') &&
    (!isset($pword) || $pword != 'AGQ@2006')
) {
    $original_url = 'http://localhost/AGQ/employdash.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_employTransactionView.php?url=' . $encoded_url);
    exit;
}
