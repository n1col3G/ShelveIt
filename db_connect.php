<?php
class Database{
    public function __construct(){
        die('Init function error');
    }

    public static function dbConnect(){
        $mysqli = null;
        require_once("/home/ngoulet/DBgoulet.php");

        if($mysqli == null){
            try{
                $mysqli = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, USERNAME, PASSWORD);
                //echo "Successful Connection";
            }catch(PDOException $e){
                echo "Could not connect";
                die($e->getMessage());
            }
        }
        return $mysqli;
    }

    public static function dbDisconnect(){
        $mysqli = null;
    }
}

?>
