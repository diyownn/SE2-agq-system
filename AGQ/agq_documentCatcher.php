<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

$refnum = isset($_GET['refNum']) ? htmlspecialchars($_GET['refNum']) : '';
$docType = isset($_GET['doctype']) ? htmlspecialchars($_GET['doctype']) : '';

require __DIR__ . '/secret/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$key = $_ENV['ENCRYPTION_KEY'];

if (!$key) {
    die("Location: UNAUTHORIZED.php?error=401k");
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


if ($docType == 'MANIFESTO') {
    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_manifestoView.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_manifestoView.php?url=' . $encoded_url . '&refNum=' . urlencode($refnum));
    exit;
} else {

    if (($role == 'Admin' || $role == 'admin' || $role == 'owner' || $role == 'Owner') && $pword != 'AGQ@2006') {

        $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ownDocumentView.php';
        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: agq_ownerDocumentView.php?url=' . $encoded_url . '&refNum=' . urlencode($refnum));
        exit;
    } else if (($role == 'Export Forwarding' || $role == 'Import Forwarding' || $role == 'Export Brokerage' || $role == 'Import Brokerage') && $pword != 'agqFreight') {


        $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_employDocumentView.php';
        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: agq_employDocumentView.php?url=' . $encoded_url . '&refNum=' . urlencode($refnum));
        exit;
    }
}
