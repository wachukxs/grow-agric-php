<?php
    require "../vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

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
            $this->database_host = $_ENV["GROW_AGRIC_HOST_NAME"];
            $this->database_name = $_ENV["GROW_AGRIC_DATABASE_NAME_TEST"]; // eventually dynamically set to prod/test
            $this->database_username = $_ENV["GROW_AGRIC_DATABASE_USER_NAME"];
            $this->database_password = $_ENV["GROW_AGRIC_DATABASE_PASSWORD"];

            $this->database_connection = null;
            try {
                $this->database_connection = new PDO('mysql:host=' . $this->database_host . ';dbname=' . $this->database_name,
                $this->database_username, $this->database_password);

                $this->database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connection Error:' . $e->getMessage();
            } catch (Throwable $th) {
                //throw $th;
            }

            return $this->database_connection;
            
        }
    }
?>