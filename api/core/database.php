<?php

namespace Core;

use \PDO;
use Exception;
use \Core\ErrorResponse as Error;

/**
 * Provide open PDO database connection via $conn property.  
 * Uses singleton pattern to ensure only one connection open at a time.
 * 
 * @category Core
 */
class Database{


    /**
     * Thhe PDO database connection. Null if connection closed or invalid.
     *
     * @var PDO|null
     */
    public PDO|null $conn;

    /**
     * Store a reference to the class instance
     *
     * @var Database
     */
    private static $instance;


    /**
     * Singleton pattern derived from code at {@link https://stackoverflow.com/a/2047999/6941165 stackoverflow}
     *
     * @return Database 
     * 
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    /**
     * Instantiate new Database class. As part of this process it test if a connection to the
     * configured database can be opened. Database configuration is set in the config.php file.
     */
    private function __construct(){

        $this->conn = null;

        try{
            $host = Config::read('db.host');
            $port = Config::read('db.port');

            if ($this->testConnection($host, $port)) {

                $this->conn = new PDO("mysql:host=" . $host . ";port=" . 
                                            $port. ";dbname=" . 
                                            Config::read('db.name') . ";charset=utf8",
                                            Config::read('db.user'),
                                            getenv(Config::read('db.password'))
                                        );

                // From https://stackoverflow.com/a/60496/6941165
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
            }
            else {
                Error::response("Database error: Connection refused by $host:$port");
            }
                
        }catch(Exception $e){
            Error::response("Database error: Connection refused by $host:$port", $e, 503);
        }
    }


    /**
     * Test opening a connection to the database.
     *
     * @param string $host The location of the database server, often '127.0.0.1'
     * @param int $port The mysql/mariadb port, usually 3306
     * 
     * @return bool 'true' if connection can be opened
     * 
     */
    private function testConnection(string $host, int $port) : bool{
        $waitTimeoutInSeconds = 1;

        if ($fp = fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)) {
            // It worked
            return true;
        } else {
            // It didn't work
            return false;
        }
        fclose($fp);
    }
}