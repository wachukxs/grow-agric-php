<?php
    include_once 'config/globals/header.php';

    # can open http://localhost:8888/grow-agric-php/
    require "vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
    $dotenv->safeLoad();

    # using getenv so it works on heroku [https://grow-agric.herokuapp.com/index.php]
    echo  "Welcome to Grow Agric's API!" . "\n" . 'If you can see this, reach us via ' . getenv("GROW_AGRIC_DEV_EMAIL") . ' for now!'; // echo 'If you can see this, reach us via ' . $_ENV["GROW_AGRIC_DEV_EMAIL"] . '!';

    // phpinfo();

    echo getenv("WEB_PUSH_PRIVATE_KEY_PEM");
    echo "\n\n";
    var_dump(getenv("WEB_PUSH_PRIVATE_KEY_PEM"));
    echo "\n\n";
    var_dump(getenv("FARMERS_PROD_BASE_URL"));
?>
