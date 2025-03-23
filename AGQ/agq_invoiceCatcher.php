<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

require __DIR__ . '/secret/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$key = "0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW"; //$_ENV['ENCRYPTION_KEY'];
echo "Key Loaded: " . $key;
if (!$key) {
    die("Location: UNAUTHORIZED.php?error=401k ");
}

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

if ($role == 'Export Brokerage') {


    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ebinvoiceNewDocument.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ebinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Export Forwarding') {

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_efinvoiceNewDocument.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_efinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Import Brokerage') {


    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ibinvoiceNewDocument.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ibinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Import Forwarding') {


    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ifinvoiceNewDocument.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ifinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
}
