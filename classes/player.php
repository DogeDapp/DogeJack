<?php

/* This file is part of Michal Schweichler's DogeJack
 * - BlackJack based on Dogecoin currency
 * 
 * @author Michal Schweichler <michal.schweichler@gmail.com>
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

// Include config for RPC details
require_once './config.inc.php';

/**
 * player
 * 
 * Basic player class
 */
class player{

    public $_id;
    public $_receiving_address;
    public $_address;
    public $_money;

    function player(){
        global $salt, $doge, $debug;
        global $config; // I'll change that later xd

        $conn = new mysqli($config['sql_host'], $config['sql_user'], $config['sql_pass'], $config['sql_db']);

        if($conn->connect_error){
            if($debug)
                "Error: " . $conn->error;
            else
                die("Sorry, an error occured and we cannot continue processing this page.");
        }

        if(isset($_GET['id'])){
            $this->_id = $_GET['id'];

            $stmt = $conn->prepare("SELECT `address`, `receiving_address` FROM `players` WHERE `id` = ?");
            $stmt->bind_param("s", $this->_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 0){
                if($debug)
                    echo "nie ma takiego id w bazie, oszukujo";
            }else if($stmt->num_rows == 1){
                $stmt->bind_result($this->_address, $this->_receiving_address);
                $stmt->fetch();
                try{
                    $resp = $doge->getaddressesbyaccount($this->_id);
                }catch(Exception $ex){
                    if($debug)
                        echo "Exception: " . $ex->getMessage();
                    else
                        die("Sorry, an error occured and we cannot continue processing this page.");
                }
                if(!empty($resp)){
                    if($resp[0] == $this->_receiving_address){
                        if($debug)
                            echo $this->_id . "<br />" . $this->_address . "<br />" . $this->_receiving_address . "<br />" . $this->_money;
                    }else{
                        if($debug)
                            echo "gracz znajduje sie bazie i rpc, ale dane sie nie zgadzaja";
                    }
                }else{
                    if($debug)
                        echo "Exception: 13"; //gracza nie ma w rpc
                    else
                        die("Sorry, an error occured and we cannot continue processing this page.");
                }
            }else if($stmt->num_rows > 1){
                if($debug)
                    echo "Exception: 14"; // gracz wystepuje wiecej niz 1 raz w bazie / nie powinno sie to zdarzyc
                else
                    die("Sorry, an error occured and we cannot continue processing this page.");
            }
        }else if(isset($_POST['address'])){
            $this->_address = $_POST['address'];
            $this->_id = hash('sha256', $this->_address . $salt . uniqid(microtime()));
            // validate the address
            try{
                $resp = $doge->validateaddress($this->_address);
            }catch(Exception $ex){
                if($debug)
                    echo "Exception: " . $ex->getMessage();
                else
                    die("Sorry, an error occured and we cannot continue processing this page.");
            }
            if($resp['isvalid']){
                try{
                    $this->_receiving_address = $doge->getnewaddress($this->_id);
                }catch(Exception $ex){
                    if($debug)
                        echo "Exception: " . $ex->getMessage();
                    else
                        die("Sorry, an error occured and we cannot continue processing this page.");
                }

                $stmt = $conn->prepare("SELECT `id` FROM `players` WHERE `address` = ?");
                $stmt->bind_param("s", $this->_address);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $stmt->bind_result($id);
                    $stmt->fetch();
                    try{
                        $doge->move($id, "Moje prywatne konto", $doge->getbalance($id));
                    }catch(Exception $ex){
                        if($debug)
                            echo "Exception: " . $ex->getMessage();
                        else
                            die("Sorry, an error occured and we cannot continue processing this page.");
                    }
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE `players` SET `id` = ?, `receiving_address` = ?, `money` = 0 WHERE `address` = ?");
                    $stmt->bind_param("sss", $this->_id, $this->_receiving_address, $this->_address);
                    $stmt->execute();

                    $conn->close();
                    header("Location: http://127.0.0.1/DogeJack/test.php?id=" . $this->_id);
                }else{
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO `players` (`id`, `receiving_address`, `address`, `money`) VALUES (?, ?, ?, 0)");
                    $stmt->bind_param("sss", $this->_id, $this->_receiving_address, $this->_address);
                    $stmt->execute();

                    $conn->close();
                    header("Location: http://127.0.0.1/DogeJack/test.php?id=" . $this->_id);
                }
            }else{
                echo "bledny adres";
            }
        }
    }

// END OF CONSTRUCTION 
    // checks transfer from player to dogejack account
    function checkMoney(){
        global $doge, $debug;
        global $config; // I'll change that later xd

        $conn = new mysqli($config['sql_host'], $config['sql_user'], $config['sql_pass'], $config['sql_db']);

        if($conn->connect_error){
            if($debug)
                "Error: " . $conn->error;
            else
                die("Sorry, an error occured and we cannot continue processing this page.");
        }

        try{
            $resp = $doge->getbalance($this->_id);
        }catch(Exception $ex){
            if($debug)
                echo "Exception: " . $ex->getMessage();
            else
                die("Sorry, an error occured and we cannot continue processing this page.");
        }
        if($resp > 0){
            $stmt = $conn->prepare("UPDATE `players` SET `money` = ? WHERE `id` = ?");
            $stmt->bind_param("ds", $resp, $this->_id);
            $stmt->execute();

            $this->_money = $resp;

            try{
                $respMove = $doge->move($this->_id, "DogeJack", $this->_money);
            }catch(Exception $ex){
                if($debug)
                    echo "Exception: " . $ex->getMessage();
                else
                    die("Sorry, an error occured and we cannot continue processing this page.");
            }

            $conn->close();
            return 1;
        }else{
            $conn->close();
            return 0;
        }
    }

}
