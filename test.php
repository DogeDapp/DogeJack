<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    </head>
<?php
/* 
 * @author Michal Schweichler <michal.schweichler@gmail.com>
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */
require_once 'config.inc.php';
require_once 'classes/player.php';

if($debug){
    echo "Owner's money: ".$doge->getbalance('DogeJack')." Ɖ<br />";
}


if(!isset($_GET['id']) && !isset($_POST['address'])){
?>
<form method="post" action="">
    <input type="text" name="address" />
    <input type="submit" value="Let's play!" />
</form>
<?php
}else if(isset($_GET['id'])){
    $p = new player();
    echo "Id (id): ".$p->_id."<br />";
    if($p->checkMoney())
        echo "doszlo ".$p->_money;
    else
        echo "sprawdz za chwile";
}else if(isset($_POST['address'])){
    $p = new player();
    echo "Id (address): ".$p->_id."<br />";
}

?>
</html>