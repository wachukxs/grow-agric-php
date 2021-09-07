<?php
    require "vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
    $dotenv->safeLoad();

    echo "Welcome to Grow Agric's API! ";
    echo 'If you can see this, reach us via ' . getenv("GROW_AGRIC_DEV_EMAIL") . '!'; // echo 'If you can see this, reach us via ' . $_ENV["GROW_AGRIC_DEV_EMAIL"] . '!';
?>
