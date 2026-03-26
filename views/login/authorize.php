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
    
    // ... keep your setup code at the top ...

if (isset($_GET['code'])) {
    // 1. Try to exchange the code for a token
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // DEBUG: See what Google actually sent back
    if (isset($accessToken['error'])) {
        echo "<h1>Google Error</h1>";
        echo "<pre>" . print_r($accessToken, true) . "</pre>";
        exit; // Stop the redirect so you can read the error
    }

    // 2. If successful, save it
    file_put_contents($tokenPath, json_encode($accessToken));
    echo "Token saved successfully!<br>";

    // 3. Try to send the email
    try {
        $client->setAccessToken($accessToken);
        $service = new Google\Service\Gmail($client);
        
        $recipientEmail = 'recon21342@gmail.com';
        $subject = 'Gmail API: Refresh Token Updated';
        $body = "The Gmail API tokens were successfully refreshed on " . date('Y-m-d H:i:s');

        $strMailContent = "From: me\r\n";
        $strMailContent .= "To: $recipientEmail\r\n";
        $strMailContent .= "Subject: $subject\r\n\r\n";
        $strMailContent .= $body;

        $mime = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($strMailContent));
        $msg = new Google\Service\Gmail\Message();
        $msg->setRaw($mime);

        $service->users_messages->send('me', $msg);
        echo "Email sent successfully to $recipientEmail!";
        
    } catch (Exception $e) {
        echo "<h1>Gmail Send Error</h1>";
        echo $e->getMessage();
    }
    
    echo "<br><a href='/auth/login'>Continue to Login</a>";
    exit; // STOP HERE so you can see the results
}

// Only redirect to Google if there is no 'code' in the URL
if (!file_exists($tokenPath)) {
    header('Location: ' . $client->createAuthUrl());
    exit;
} else {
    echo "You already have a token. Delete token.json if you want to re-authenticate.";
}

}

// Redirect to Google if no code is present
header('Location: ' . $client->createAuthUrl());
exit;