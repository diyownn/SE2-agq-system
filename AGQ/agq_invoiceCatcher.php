<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

if ($role == 'Export Brokerage') {

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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ebinvoiceNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ebinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Export Forwarding') {

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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_efinvoiceNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_efinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Import Brokerage') {



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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ibinvoiceNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ibinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
} else if ($role == 'Import Forwarding') {

    $_SESSION['redirected'] = true;

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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ifinvoiceNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ifinvoiceNewDocument.php?url=' . $encoded_url);
    exit;
}
