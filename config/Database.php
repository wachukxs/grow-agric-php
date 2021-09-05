<?php
    class Database {
        // Database parameters
        private $database_host = '';
        private $database_name = '';
        private $database_username = '';
        private $database_password = '';
        private $database_connection;

        public function connect() // must be public [& not __construct, cause it'll return a Database data type not PDO], else we can't call it elsewhere
        {
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