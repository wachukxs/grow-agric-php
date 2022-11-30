<?php
// Resources
include_once __DIR__ . "/../config/Database.php";
include_once __DIR__ . "/../model/Records.php";


require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

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
            // then decode
            $this->subscription_data = json_decode($this->subscription_data);
        }
    }
}

$auth = [
    'VAPID' => [
        // TODO: replace our mail with get env
        'subject' => 'mailto:hello@growagric.com', // can be a mailto: or your website address
        'publicKey' => getenv("WEB_PUSH_PUBLIC_KEY"), // (recommended) uncompressed public key P-256 encoded in Base64-URL
        'privateKey' => getenv("WEB_PUSH_PRIVATE_KEY"), // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
        // 'pemFile' => 'path/to/pem', // if you have a PEM file and can link to it on your filesystem
        // 'pem' => trim(getenv("WEB_PUSH_PRIVATE_KEY_PEM"), "\""), // if you have a PEM file and want to hardcode its content // maybe not or trim(getenv("WEB_PUSH_PRIVATE_KEY_PEM"), "\"") // || '"'
    ],
];

$webPush = new WebPush($auth);
$webPush->setReuseVAPIDHeaders(true);

function sendNewMessageNotification($farmerid, $from = NULL, $message = NULL)
{
    // try-catch block so we don't break execution
    try {
        global $webPush; // use $webPush decleared outside fun
        global $records;
        // get farmer details [push data]
        $result =  $records->getFarmerPushNotificationData($farmerid);

        $_r = $result->fetchAll(PDO::FETCH_CLASS, "CleanWebPushData");
        file_put_contents('php://stderr', "\nwho rrrrrr:::: " . "\n" . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents('php://stderr', print_r($_r, TRUE) , FILE_APPEND | LOCK_EX);
        
        if (is_array($_r) && count($_r) > 0) {
            // send message

            $_farmerdata = $_r[0];

            file_put_contents('php://stderr', "\nsending web push:::: " . "\n" . "\n", FILE_APPEND | LOCK_EX);
            file_put_contents('php://stderr', "\n\n", FILE_APPEND | LOCK_EX);
            // file_put_contents('php://stderr', "so what is endpoint??\n" . "\n", FILE_APPEND | LOCK_EX);

            // create subscription
            $subscription = Subscription::create([
                "endpoint" => $_farmerdata->subscription_data->endpoint,
                // "contentEncoding" => "aesgcm", // not complusory || depends
                // "authToken" => $__r['keys']['auth'],
                "keys" => [
                    "auth" => $_farmerdata->subscription_data->keys->auth,
                    "p256dh" => $_farmerdata->subscription_data->keys->p256dh
                ]
            ]);

            // create payload
            $_payload["notification"] = array();
            $_payload["notification"]["title"] = "New Message";
            $_payload["notification"]["body"] = "Admin just sent you a message";
            $_payload["notification"]["icon"] = "assets/grow-agric-logo-square.png";
            $_payload["notification"]["vibrate"] = [100, 50, 100];
            
            // "notification": {
            //     "title": "Angular News",
            //     "body": "Newsletter Available!",
            //     "icon": "assets/main-page-logo-small-hat.png",
            //     "vibrate": [100, 50, 100],
            //     "data": {
            //         "dateOfArrival": Date.now(),
            //         "primaryKey": 1
            //     },
            //     "actions": [{
            //         "action": "explore",
            //         "title": "Go to the site"
            //     }]
            // }


            /**
             * send one notification and flush directly
             * @var MessageSentReport $report
             */
            $report = $webPush->sendOneNotification(
                $subscription,
                json_encode($_payload), // optional (defaults null)
                [
                    'TTL' => 5000,
                    'topic' => 'new_message',
                ]
            );

            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                // file_put_contents('php://stderr', print_r("Push Notification Message sent successfully for subscription {$endpoint}." . "\n", TRUE) , FILE_APPEND | LOCK_EX);
                file_put_contents('php://stderr', print_r("Push Notification Message sent successfully for subscription" . "\n", TRUE) , FILE_APPEND | LOCK_EX);
            } else {
                file_put_contents('php://stderr', print_r("Push Notification Message Message failed to sent for subscription, farmer id {$farmerid}: {$report->getReason()}" . "\n", TRUE) , FILE_APPEND | LOCK_EX);

                // duplicate
                // file_put_contents('php://stderr', print_r("\nPush http response: {$report->getResponse()}" . "\n", TRUE) , FILE_APPEND | LOCK_EX);
                file_put_contents('php://stderr', print_r("\nis Push sub expired: {$report->isSubscriptionExpired()}" . "\n", TRUE) , FILE_APPEND | LOCK_EX);
                file_put_contents('php://stderr', print_r("\nPush http response: {$report->getResponse()}" . "\n", TRUE) , FILE_APPEND | LOCK_EX);
            }


        } else {
            // do nothing??
        }
    } catch (\Throwable $err) {
        file_put_contents('php://stderr', "\nWebPushNotifications.php->sendNewMessageNotification() ERR: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents('php://stderr', print_r('Connection Error at Line:' . $err->getLine() . "\n", TRUE));
                
        file_put_contents('php://stderr', print_r('Connection Error Code:' . $err->getCode() . "\n", TRUE));

        return false;
    }
    

}