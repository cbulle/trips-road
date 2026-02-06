<?php
require ROOTFORM . "/googleForm/oauthConfig.php";

if (!isset($_GET['code'])) {
    exit('login failed');
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token['access_token']);

$oauth = new Google_Service_Oauth2($client);

$userData = $oauth->userinfo->get();

