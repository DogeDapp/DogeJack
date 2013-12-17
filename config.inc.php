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
$config['sql_host'] = 'localhost';
$config['sql_user'] = 'root';
$config['sql_pass'] = '';
$config['sql_db'] = 'dogejack';


// Dogecoin wallet RPC config and connection
$rpc_host = '127.0.0.1';
$rpc_port = '22555';
$rpc_user = 'dogowy';
$rpc_pass = '';
require_once 'classes/jsonRPCClient.php';
$doge = new jsonRPCClient('http://'.$rpc_user.':'.$rpc_pass.'@'.$rpc_host.':'.$rpc_port);

// Owner dogecoin address

$owner_address = 'DCGWjTnZQLroppWaWtA5Xq3u5aX6Ge7AYK';


// Blackjack config
define('MIN_BET', 1);
define('MAX_BET', 500);


// salt for sha256
$salt = 'Wu5hUbREFU2RecRaChecaT2ar6pe7ezA';

