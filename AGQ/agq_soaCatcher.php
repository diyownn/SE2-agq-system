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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ebsoaNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ebsoaNewDocument.php?url=' . $encoded_url);
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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_efsoaNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_efsoaNewDocument.php?url=' . $encoded_url);
    exit;

    exit();
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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ibsoaNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ibsoaNewDocument.php?url=' . $encoded_url);
    exit;

    exit();
} else if ($role == 'Import Forwarding') {

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

    $original_url = 'http://localhost/SE2-agq-system/AGQ/agq_ifsoaNewDocument.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_ifsoaNewDocument.php?url=' . $encoded_url);
    exit;

    exit();
}
