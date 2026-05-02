<?php
    require_once __DIR__.'/bootstrap.php';

    class Database{
        private $host;
        private $db_name;
        private $username;
        private $password;
        private $port;
        private $conn;

        public function __construct(){
            $this->conn=null;

            try{
                $this->conn=new PDO("mysql:host=".$this->host.";port=".$this->port.";dbname=".$this->db_name,
                $this->username,
                $this->password);
                $this->conn->exec("set names utf8mb4");

                $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                error_log("Connection error:").$e->getMessage().((int)$e->getCode());
                return null;
            }
            return $this->conn;
        }
    }
