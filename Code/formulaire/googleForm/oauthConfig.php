<?php

require_once WEBROOT . "../vendor/autoload.php";

$client = new Google\Client();
$client->setClientId("995499019090-anmh1d4m4obifri1fs9egue2p5417f8h.apps.googleusercontent.com");
$client-> setClientSecret("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");
$client-> setRedirectUri( "http://localhost:8080/");

$client->addScope("email");
$client->addScope("profile");