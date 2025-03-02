<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

if ($role == 'Export Brokerage') {
    header("location:HTML (needs backend)/ebinvoice.html");

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

        $original_url = 'http://localhost/SE2-agq-system/AGQ/HTML%20(needs%20backend)/ebinvoice.html';
        $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: HTML (needs backend)/ebinvoice.html?url=' . $encoded_url);
        exit;
    } else {

        unset($_SESSION['redirected']);
    }
    exit();
}else if ($role == 'Export Forwarding') {
    header("location:HTML (needs backend)/efinvoice.html");

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

        $original_url = 'http://localhost/SE2-agq-system/AGQ/HTML%20(needs%20backend)/efinvoice.html';
        $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: HTML (needs backend)/efinvoice.html?url=' . $encoded_url);
        exit;
    } else {

        unset($_SESSION['redirected']);
    }
    exit();
}else if ($role == 'Import Brokerage') {
    header("location:HTML (needs backend)/ibinvoice.html");

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

        $original_url = 'http://localhost/SE2-agq-system/AGQ/HTML%20(needs%20backend)/ibinvoice.html';
        $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: HTML (needs backend)/ibinvoice.html?url=' . $encoded_url);
        exit;
    } else {

        unset($_SESSION['redirected']);
    }
    exit();
}else if ($role == 'Import Forwarding') {
    header("location:HTML (needs backend)/ifinvoice.html");

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

        $original_url = 'http://localhost/SE2-agq-system/AGQ/HTML%20(needs%20backend)/ifinvoice.html';
        $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: HTML (needs backend)/ifinvoice.html?url=' . $encoded_url);
        exit;
    } else {

        unset($_SESSION['redirected']);
    }
    exit();
} 
