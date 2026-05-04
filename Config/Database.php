<?php

    //Include the bootstrap file(initializing environment variables)
    require_once __DIR__ . '/../bootstrap.php';

    //Define a Database class to manage database connections
    class Database{
    //Class properties for database configuration
    private $host; //Database Host
    private $db_name;//Database name
    private $username;//Username
    private $password;//Database password
    private $port;//Database port
    private $conn;//PDO connection object

    //Constructor method that initializes database configuration from environment variables
    public function __construct(){
        //Get database credentials from environment variables
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->port = $_ENV['DB_PORT'];
    }

    //Method to establish and return database connection
    public function getConnection(){
        try {
            //Create a new PDO instance with the given database credentials and settings
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            //Set PDO to throw exceptions on errors
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            //Disable emulated prepared statements
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $this->conn;
        } catch (PDOException $e) {
            //Log the connection error message
            error_log("Connection error: " . $e->getMessage());
            //Return null if connection failed
            return null;
        }
    }
}