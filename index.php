<?php
// only for debugging
error_reporting(E_ALL);

// Load classes
require_once 'DogeJack.php';

// Start session
session_start();

// Sanitize input, prevent XSS
$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


function getCardImage($card){
    $image = '<img src="cards/';
    
    switch($card->getRank()){
        case Rank::ACE:
            $image .= 'A';
            break;
        case Rank::KING:
            $image .= 'K';
            break;
        case Rank::QUEEN:
            $image .= 'Q';
            break;
        case Rank::JACK:
            $image .= 'J';
            break;
        default:
            $image .= $card->getRank();
    }
    
    switch($card->getSuit()){
        case Suit::CLUBS:
            $image .= 'C';
            break;
        case Suit::DIAMONDS:
            $image .= 'D';
            break;
        case Suit::HEARTS:
            $image .= 'H';
            break;
        case Suit::SPADES:
            $image .= 'S';
            break;
        default:
            break;
    }
    
    $image .= '.png" />';
    
    return $image;
}


if(isset($_SESSION['game']) && !empty($_SESSION['game']) && !isset($_POST['reset'])){
    $game = $_SESSION['game'];
    $p1 = $_SESSION['p1'];
    $dealer = $_SESSION['dealer'];
}else{
    $p1 = new Player();
    $dealer = new Dealer();

    $game = new Game($dealer, $p1);
    
    $_SESSION['game'] = $game;
    $_SESSION['p1'] = $p1;
    $_SESSION['dealer'] = $dealer;
}



if($game->getState() === State::READY_TO_RESTART && isset($_POST['playAgain']) && $_POST['playAgain'] === 'Play Again'){
    $game->restart();
}

if($game->getState() === State::START && isset($_POST['deal']) && $_POST['deal'] === 'DEAL'){ // deal button was clicked
    if($game->possibleActions()['deal'] === TRUE){ // check if deal is possible
        if($p1->checkMoney($_POST['betValue'])){ // check if player has enough money for requested bet
            $dealer->dealCards($p1, $_POST['betValue']);
            $_SESSION['lastBet'] = $_POST['betValue'];
            $game->updateState();
        }else{
            // NOT ENOUGH MONEY
        }
    }
}

if($game->getState() === State::OFFER_INSURANCE){
    if(isset($_POST['buyInsurance']) && $_POST['buyInsurance'] === 'Buy Insurance'){
        $p1->setInsurance(TRUE);
        $game->updateState();
    }elseif(isset($_POST['rejectInsurance']) && $_POST['rejectInsurance'] === 'Reject Insurance'){
        $p1->setInsurance(FALSE);
        $game->updateState();
    }
}

if($game->getState() === State::PLAYING){
    if(isset($_POST['hit']) && $_POST['hit'] === 'Hit'){
        $dealer->dealNextCard($p1);
        $game->updateState();
    }
    if(isset($_POST['stand']) && $_POST['stand'] === 'Stand'){
        $p1->Stand();
        $game->updateState();
    }
}

if($game->getState() === State::PLAYER_ACTION){
    if(isset($_POST['hit']) && $_POST['hit'] === 'Hit'){
        $p1->setDouble(FALSE);
        $dealer->dealNextCard($p1);
        $game->updateState();
    }
    if(isset($_POST['stand']) && $_POST['stand'] === 'Stand'){
        $p1->setDouble(FALSE);
        $p1->Stand();
        $game->updateState();
    }
    if(isset($_POST['double']) && $_POST['double'] === 'Double'){
        $p1->setDouble(TRUE);
        $dealer->dealNextCard($p1);
        $game->updateState();
    }
}

while($game->getState() === State::DEALER_ACTION){
    $dealer->dealNextCard($dealer);
    $game->updateState();
}

if($game->getState() == State::FINALIZE){
    echo $game->reconcile();
    $game->updateState();
}


?>
<?php if($game->getState() > State::START){ ?>
<div id="cards">
    <div id="player">
        You have: <?php foreach($p1->getHand()->getCards() as $card){ echo getCardImage($card); } ?>
    </div>
    <div id="dealer">
        Dealer has: <?php if($game->getState() < State::DEALER_ACTION){ echo getCardImage($dealer->getUpCard()); }else{ foreach($dealer->getHand()->getCards() as $card){ echo getCardImage($card); } } ?>
    </div>
</div>
<?php } ?>


<div id="playerStats">
    <?php 
    if($game->getState() !== State::FINALIZE && $game->getState() !== State::READY_TO_RESTART)
        echo 'Balance: '.($p1->getMoney()-$p1->getBetValue()-$p1->getInsuranceValue());
    else
        echo 'Balance: '.$p1->getMoney();
    ?>
    <?php echo ' Bet: '.$p1->getBetValue().' Insurance: '.$p1->getInsuranceValue().' Win: '.$p1->_winValue; ?>
</div>
<div id="gameNotice">
    Dealer: (<?php echo (sizeof($dealer->getTotals()) > 1) ? $dealer->getTotals()[0].'/'.$dealer->getTotals()[1] : $dealer->getTotals()[0]; ?>)
    </br>
    You: (<?php echo (sizeof($p1->getTotals()) > 1) ? $p1->getTotals()[0].'/'.$p1->getTotals()[1] : $p1->getTotals()[0]; ?>)
</div>
<form action="" method="post">
    <select name="betValue" <?php echo $game->possibleActions()['betValue'] ? '' : 'disabled'; ?>>
        <?php 
        if($game->getState() > State::START)
            echo '<option selected>'.$p1->getInitialBetValue().'</option>'; 
        else
            if(isset($_SESSION['lastBet']) && !empty($_SESSION['lastBet']))
                echo '<option selected>'.$_SESSION['lastBet'].'</option>';
        ?>
        <option>1</option>
        <option>5</option>
        <option>25</option>
        <option>125</option>
        <option>500</option>
        <?php echo '<option>'.$p1->getMoney().'</option>'; ?>
    </select>
    <input name="deal" type="submit" value="DEAL" <?php echo $game->possibleActions()['deal'] ? '' : 'disabled'; ?>/>
    <input name="hit" type="submit" value="Hit" <?php echo $game->possibleActions()['hit'] ? '' : 'disabled'; ?>/>
    <input name="stand" type="submit" value="Stand" <?php echo $game->possibleActions()['stand'] ? '' : 'disabled'; ?>/>
    <input name="double" type="submit" value="Double" <?php echo $game->possibleActions()['double'] ? '' : 'disabled'; ?>/>
    <input name="buyInsurance" type="submit" value="Buy Insurance" <?php echo $game->possibleActions()['buyInsurance'] ? '' : 'disabled'; ?>/>
    <input name="rejectInsurance" type="submit" value="Reject Insurance" <?php echo $game->possibleActions()['rejectInsurance'] ? '' : 'disabled'; ?>/>
    <input name="playAgain" type="submit" value="Play Again" <?php if($game->getState() !== State::READY_TO_RESTART) echo 'disabled'; ?>/>
    <!-- ONLY FOR DEBUG -->
    <input name="reset" type="submit" value="reset" />
    <!-- /ONLY FOR DEBUG -->
</form>

<?php 
$game->debug();
echo $p1->hasBlackjack();
?>