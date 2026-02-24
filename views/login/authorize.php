<?php
$rootPath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2); 
require_once $rootPath . '/vendor/autoload.php';

$client = new Google\Client();
$credentialsPath = $rootPath . '/credentials.json';
$tokenPath = $rootPath . '/token.json';

// Basic setup
$client->setAuthConfig($credentialsPath);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->addScope(Google\Service\Gmail::GMAIL_SEND);

// Determine redirect URI automatically based on environment
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$client->setRedirectUri($protocol . $_SERVER['HTTP_HOST'] . '/auth/authorize');

if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($accessToken['error'])) {
        file_put_contents($tokenPath, json_encode($accessToken));
    }

    // Redirect to login after processing
    header('Location: /auth/login');
    exit;
}

// Redirect to Google if no code is present
header('Location: ' . $client->createAuthUrl());
exit;