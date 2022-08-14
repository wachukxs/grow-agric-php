<?php
// Resources
include_once __DIR__ . "/../config/Database.php";
include_once __DIR__ . "/../model/Records.php";


require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Records object
// https://www.geeksforgeeks.org/how-to-declare-a-global-variable-in-php
$records = new Records($a_database_connection);

# Sources:
# https://github.com/web-push-libs/web-push-php
# https://web.dev/push-notifications-subscribing-a-user
# https://web.dev/push-notifications-permissions-ux/

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;


class CleanWebPushData {

    public function __construct()
    {
        if ($this->subscription_data) {
            $this->subscription_data = htmlspecialchars_decode($this->subscription_data, ENT_QUOTES);
        }
    }
}

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

function sendNewMessageNotification($farmerid, $from = NULL, $message = NULL)
{
    global $webPush; // use $webPush decleared outside fun
    // get farmer details [push data]
    $result =  $GLOBALS['records']->getFarmerPushNotificationData($farmerid);
        
    $_r = $result->fetchAll(PDO::FETCH_CLASS, "CleanWebPushData");

    if (is_array($_r) && count($_r) > 0) {
        // send message

        $_farmerwebpushdata = json_decode($_r[0]);
        // create subscription
        $subscription = Subscription::create([
            "endpoint" => $_farmerwebpushdata->endpoint,
            // "contentEncoding" => "aesgcm", // not complusory || depends
            // "authToken" => $__r['keys']['auth'],
            "keys" => [
                "auth" => $_farmerwebpushdata['keys']['auth'],
                "p256dh" => $_farmerwebpushdata['keys']['p256dh']
            ]
        ]);

        // create payload
        $_payload["message"] = "Hello there!";


        /**
         * send one notification and flush directly
         * @var MessageSentReport $report
         */
        $report = $webPush->sendOneNotification(
            $subscription,
            json_encode($_payload) // optional (defaults null)
        );


    } else {
        // do nothing??
    }
    

}