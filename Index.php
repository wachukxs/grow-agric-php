<?php
    require "vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    echo "Welcome to Grow Agric's API! ";
    echo 'If you can see this, reach us via ' . $_ENV["GROW_AGRIC_DEV_EMAIL"] . '!';
?>
