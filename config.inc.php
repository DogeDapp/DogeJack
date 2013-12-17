<?php

/* 
 * @author Michal Schweichler <michal.schweichler@gmail.com>
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

// CONFIGURATION 



// debug mode on/off
$debug = true;
if($debug)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(0);
}


// MySQL config
$sql_host = 'localhost';
$sql_user = 'root';
$sql_pass = '';
$sql_db = 'dogejack';


// Dogecoin wallet RPC config
$wallet_host = '127.0.0.1';
$wallet_port = '22555';
$wallet_user = 'dog';
$wallet_pass = 'wow';


// Owner dogecoin address
$owner_address = '';


// Blackjack config
define('MIN_BET', 1);
define('MAX_BET', 500);




?>