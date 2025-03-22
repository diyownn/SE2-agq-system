<?php
session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

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
    $original_url = 'http://localhost/SOFT%20ENG/agq_manifestoView.php';
    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: agq_manifestoView.php?url=' . $encoded_url . '&refnum=' . $refnum);
    exit;
} else {

    if (($role == 'Admin' || $role == 'admin' || $role == 'owner' || $role == 'Owner') && $pword != 'agqLogistics') {


        $original_url = 'http://localhost/SOFT%20ENG/owndash.php';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: agq_owndash.php?url='  . $encoded_url . '&refnum=' . $refnum);
        exit;
    } else if (($role == 'Export Forwarding' || $role == 'Import Forwarding' || $role == 'Export Brokerage' || $role == 'Import Brokerage') && $pword != 'agqFreight') {

        $original_url = 'http://localhost/SOFT%20ENG/employdash.php';

        $encrypted_url = encrypt_url($original_url, $key);
        $encoded_url = urlencode($encrypted_url);

        header('Location: agq_employdash.php?url=' . $encoded_url . '&refnum=' . $refnum);
        exit;
    };
}
