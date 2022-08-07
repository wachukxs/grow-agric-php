<?php

require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();



# Sources:
# https://github.com/web-push-libs/web-push-php
# https://web.dev/push-notifications-subscribing-a-user
# https://web.dev/push-notifications-permissions-ux/

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$endpoint = 'https://fcm.googleapis.com/fcm/send/abcdef...'; // Chrome

$auth = [
    'VAPID' => [
        // TODO: replace our mail with get env
        'subject' => 'mailto:hello@growagric.com', // can be a mailto: or your website address
        'publicKey' => getenv("WEB_PUSH_PUBLIC_KEY"), // (recommended) uncompressed public key P-256 encoded in Base64-URL
        'privateKey' => getenv("WEB_PUSH_PRIVATE_KEY"), // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
        // 'pemFile' => 'path/to/pem', // if you have a PEM file and can link to it on your filesystem
        'pem' => trim(getenv("WEB_PUSH_PRIVATE_KEY_PEM"), "\""), // if you have a PEM file and want to hardcode its content // maybe not or trim(getenv("WEB_PUSH_PRIVATE_KEY_PEM"), "\"") // || '"'
    ],
];

$webPush = new WebPush($auth);
$webPush->setReuseVAPIDHeaders(true);