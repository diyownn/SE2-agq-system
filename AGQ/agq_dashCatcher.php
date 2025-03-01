<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';


if ($role == 'admin' || $role == 'Admin' || $role == 'owner' || $role == 'Owner' && $pword != 'agqLogistics') {
    header("location:agq_owndash.php");

    if (!isset($_SESSION['redirected'])) {
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

        $original_url = 'http://localhost/SOFT%20ENG/owndash.php';
        $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: agq_owndash.php?url=' . $encoded_url);
        exit;
    } else {

        unset($_SESSION['redirected']);
    }
    exit();
} else if ($role == 'Export Forwarding' || $role == 'Import Forwarding' || $role == 'Export Brokerage' || $role == 'Import Brokerage' && $pword != 'agqLogistics') {
    header("location:agq_employdash.php");
    if (!isset($_SESSION['redirected'])) {
        $_SESSION['redirected'] = true;
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

    $original_url = 'http://localhost/SOFT%20ENG/employdash.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_employdash.php?url=' . $encoded_url);
    exit;
} else {

    unset($_SESSION['redirected']);
}
exit();
