<?php
    require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
    $dotenv->safeLoad();

    class Database {
        // Database parameters
        private $database_host;
        private $database_name;
        private $database_username;
        private $database_password;
        private $database_connection;

        public function connect() // must be public [& not __construct, cause it'll return a Database data type not PDO], else we can't call it elsewhere
        {
            # should this block be in a constructor method or sth?
            if (getenv("CURR_ENV") === "production") {
                $this->database_host = getenv("GROW_AGRIC_HOST_NAME");
                $this->database_name = getenv("GROW_AGRIC_DATABASE_NAME_PROD"); // eventually dynamically set to prod/test
                $this->database_username = getenv("GROW_AGRIC_DATABASE_USER_NAME");
                $this->database_password = getenv("GROW_AGRIC_DATABASE_PASSWORD");
            } else {
                $this->database_host = getenv("GROW_AGRIC_HOST_NAME_LOCAL"); // getenv("GROW_AGRIC_HOST_NAME");
                $this->database_name = getenv("GROW_AGRIC_DATABASE_NAME_TEST"); // eventually dynamically set to prod/test
                $this->database_username = getenv("GROW_AGRIC_DATABASE_USER_NAME_TEST");
                $this->database_password = getenv("GROW_AGRIC_DATABASE_PASSWORD_TEST");
            }
            

            $this->database_connection = null;
            try {
                $this->database_connection = new PDO('mysql:host=' . $this->database_host . ';dbname=' . $this->database_name . ';port=8889',
                $this->database_username, $this->database_password);

                $this->database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo 'Connection successful';
            } catch (PDOException $e) {
                echo 'Connection Error:' . $e->getMessage();
            } catch (Throwable $th) {
                throw $th;
            }

            return $this->database_connection;
            
        }
    }
?>