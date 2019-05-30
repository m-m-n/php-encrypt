<?php

$baseurl = 'https://example.com/';

$key_get = "{$baseurl}crypt_sample/api/key/get";

$json = file_get_contents($key_get);
$obj = json_decode($json);
$pub_key = $obj->key;

$pass = mkpw();
$c_pass = pkey_encrypt($pass, $pub_key);

$iv = mkiv();

$msg = 'くもり時々はれ';
$algo = 'aes-256-cbc';
$data = openssl_encrypt($msg, $algo, $pass, OPENSSL_RAW_DATA, $iv);

$args = json_encode([
    'key' => base64_encode($c_pass),
    'data' => base64_encode($data),
    'algo' => $algo,
    'iv' => base64_encode($iv),
]);

$send_url = "{$baseurl}crypt_sample/api/data/echo";

$c = curl_init();
curl_setopt_array($c, [
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $args,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_URL => $send_url,
]);
$r = curl_exec($c);
var_dump($r);
curl_close($c);

function mkpw()
{
    $salt = substr(md5(uniqid(), true), 0, 2);
    return hash('sha256', $salt.mt_rand(), true).hash('sha256', $salt.mt_rand(), true);
}

function pkey_encrypt($data, $pub_key)
{
    openssl_public_encrypt($data, $crypt, $pub_key);
    return $crypt;
}

function mkiv()
{
    $salt = substr(md5(uniqid(), true), 0, 2);
    return substr(hash('sha256', $salt.mt_rand(), true), 0, 16);
}
