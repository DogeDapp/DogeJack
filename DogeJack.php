<?php
class Rank{
    const ACE = 1;
    const JACK = 11;
    const QUEEN = 12;
    const KING = 13;
    
    static function randomRank(){
        return rand(1, 13);
    }
}

class Suit{
    const SPADES = 1;
    const HEARTS = 2;
    const DIAMONDS = 3;
    const CLUBS = 4;
    
    static function randomSuit(){
        return rand(1, 4);
    }
}

class Card{
    private $_rank;
    private $_suit;
    
    public function __construct($rank = NULL, $suit = NULL){
        $rank === NULL ? $this->_rank = Rank::randomRank() : $this->_rank = $rank;
        $suit === NULL ? $this->_suit = Suit::randomSuit() : $this->_suit = $suit;
    }
    
    public function getRank(){
        return $this->_rank;
    }
    
    public function getSuit(){
        return $this->_suit;
    }
    
    public function debug(){
        echo '<pre>';
        print_r($this->_rank);
        print_r($this->_suit);
        echo '</pre>';
    }
}

class Hand{
    private $_cards;
    
    public function __construct(){
        $this->_cards = array();
    }
    
    public function addCard(Card $card){
        array_push($this->_cards, $card);
    }
    
    public function getCards(){
        return $this->_cards;
    }

    public function debug(){
        echo '<pre>';
        print_r($this->_cards);
        echo '</pre>';
    }
}


class Player{
    private $_hand;
    private $_money;
    private $_initialBet; 
    private $_bet; // bet value
    private $_insurance; // true, if player bought insurance
    private $_insuranceValue; // half of initial bet
    private $_double; // true, if player is double down
    private $_stand; // true, if player stands
    public $_winValue;
    
    
    public function __construct(){
        $this->_hand = new Hand();
        $this->_bet = 0; // initial value
        $this->_insurance = NULL; // initial value
        $this->_insuranceValue = 0; // initial value
        $this->_double = NULL; // initial value
        $this->_stand = NULL; // initial value
        $this->_winValue = 0;
        $this->_money = 750; /// ONLY FOR TEST
    }
    
    public function getHand(){
        return $this->_hand;
    }
    
    public function checkMoney($money){
        return $this->_money >= $money ? TRUE : FALSE;
    }
    
    public function addMoney($value){
        $this->_money += $value;
    }
    
    public function getMoney(){
        return $this->_money;
    }
    
    public function setBet($value){
        //$this->_money -= $value;
        $this->_bet = $value;
        $this->_initialBet = $value;
    }
    
    public function getBetValue(){
        return $this->_bet;
    }
    
    public function getInitialBetValue(){
        return $this->_initialBet;
    }
    
    public function setInsurance($bool){
        $this->_insurance = $bool;
        ($bool) ? $this->_insuranceValue = $this->_bet/2 : $this->_insuranceValue = 0;
    }
    
    public function getInsuranceValue(){
        return $this->_insuranceValue;
    }
    
    public function isInsuranced(){
        return ($this->_insurance) ? TRUE : FALSE;
    }
    
    public function setDouble($bool){
        $this->_double = $bool;
        ($bool) ? $this->_bet = $this->_initialBet*2 : $this->_bet = $this->_initialBet;
    }
    
    public function isDouble(){
        return ($this->_double) ? TRUE : FALSE;
    }
    
    public function Stand(){
        $this->_stand = TRUE;
    }
    
    public function isStand(){
        return ($this->_stand) ? TRUE : FALSE;
    }

    public function isBusted(){
        return ($this->getTotals()[0] > 21) ? TRUE : FALSE;
    }
    
    public function hasBlackjack(){
        $cards = $this->getHand()->getCards();
        $totals = $this->getTotals();
        
        return (sizeof($cards) == 2 && array_pop($totals) == 21) ? TRUE : FALSE;
        //return ((sizeof($this->getHand()->getCards()) == 2) && ($this->getTotals()[1] == 21)) ? TRUE : FALSE;
        //return ((sizeof($this->getHand()->getCards()) == 2) && (array_pop($this->getTotals()) == 21)) ? TRUE : FALSE;
    }
    
    public function getHandPoints(){
        if(sizeof($this->getTotals()) == 2){
            if($this->getTotals()[1] > 21)
                return $this->getTotals()[0];
            else
                return $this->getTotals()[1];
        }else{
            return $this->getTotals()[0];
        }
    }

    public function getTotals(){
        $totals = array();
        $cards = array();
        foreach($this->_hand->getCards() as $card){
            $rank = $card->getRank();
            if($rank === Rank::JACK || $rank === Rank::QUEEN || $rank === Rank::KING)
                array_push($cards, 10);
            else
                array_push($cards, $rank);
        }
        $sum = array_sum($cards);
        array_push($totals, $sum);
        if($sum<=11){
            if(in_array(1, $cards))
                array_push($totals, $sum+10);
        }
        return $totals;
    }

    public function restart(){
        $this->_hand = new Hand();
        $this->_bet = 0; // initial value
        $this->_insurance = NULL; // initial value
        $this->_insuranceValue = 0; // initial value
        $this->_double = NULL; // initial value
        $this->_stand = NULL; // initial value
        $this->_winValue = 0;
    }
    
    public function debug(){
        echo '<pre>';
        print_r($this->_hand);
        echo '</pre>';
    }
}

class Dealer{
    private $_hand;
    
    public function __construct(){
        $this->_hand = new Hand();
    }
    
    public function getHand(){
        return $this->_hand;
    }
    
    public function getUpCard(){
        return $this->getHand()->getCards()[0];
    }

    public function dealCards(Player $player, $betValue){
        $player->setBet($betValue);
        $this->getHand()->addCard(new Card());
        $player->getHand()->addCard(new Card()); // THIS IS
        $player->getHand()->addCard(new Card()); // CORRECT
        //$player->getHand()->addCard(new Card(Rank::QUEEN, Suit::DIAMONDS));
        //$player->getHand()->addCard(new Card(Rank::QUEEN, Suit::SPADES));
    }
    
    public function dealNextCard($player){
        $player->getHand()->addCard(new Card());
    }
    
    public function isBusted(){
        return ($this->getTotals()[0] > 21) ? TRUE : FALSE;
    }
    
    public function hasBlackjack(){
        $cards = $this->getHand()->getCards();
        $totals = $this->getTotals();
        
        return (sizeof($cards) == 2 && array_pop($totals) == 21) ? TRUE : FALSE;
        //return ((sizeof($this->getHand()->getCards()) == 2) && ($this->getTotals()[1] == 21)) ? TRUE : FALSE;
    }
    
    public function getHandPoints(){
        if(sizeof($this->getTotals()) == 2){
            if($this->getTotals()[1] > 21)
                return $this->getTotals()[0];
            else
                return $this->getTotals()[1];
        }else{
            return $this->getTotals()[0];
        }
    }
    
    public function getTotals(){
        $totals = array();
        $cards = array();
        foreach($this->_hand->getCards() as $card){
            $rank = $card->getRank();
            if($rank === Rank::JACK || $rank === Rank::QUEEN || $rank === Rank::KING)
                array_push($cards, 10);
            else
                array_push($cards, $rank);
        }
        $sum = array_sum($cards);
        array_push($totals, $sum);
        if($sum<=11){
            if(in_array(1, $cards))
                array_push($totals, $sum+10);
        }
        return $totals;
    }

    public function restart(){
        $this->_hand = new Hand();
    }

    public function debug(){
        echo '<pre>';
        print_r($this->_hand);
        echo '</pre>';
    }
}

abstract class State{
    const INVALID = -1;
    const START = 0;
    const OFFER_INSURANCE = 1;
    const PLAYER_ACTION = 2;
    const PLAYING = 3; // needed to disable 'Double Down' after Hit
    const DEALER_ACTION = 4;
    const FINALIZE = 5;
    const READY_TO_RESTART = 6;
    
    const PLAYER_BUSTED = 101;
    const PLAYER_WON_BLACKJACK = 102;
    const PLAYER_WON_DEALER_BUSTED = 103;
    const PLAYER_WON_MORE_POINTS = 104;
    
    const DEALER_WON_BLACKJACK = 105;
    const DEALER_WON_MORE_POINTS = 106;
    
    const TIE_BLACKJACK = 107;
    const TIE_POINTS = 108;
}

class Game{
    private $_dealer;
    private $_player; // Could be an array for multiplayer
    private $_state;
    private $_possibleActions;
    
    public function __construct(Dealer $dealer, Player $player1){
        $this->_dealer = $dealer;
        $this->_player = $player1;
        $this->_possibleActions = array('betValue' => TRUE, 'deal' => TRUE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
        $this->_state = State::START;
    }
    
    public function setState($state){
        $this->_state = $state;
    }
    
    public function getState(){
        return $this->_state;
    }
    
    public function reconcile(){
        if($this->_player->isBusted()){
            $value = 0;
            if($this->_player->isInsuranced()){
                $value -= $this->_player->getInsuranceValue();
            }
            $value -= $this->_player->getBetValue();
            $this->_player->_winValue += $value;
            $this->_player->addMoney($value);
            return 'Player busted - dealer won - player lost '.$value;
        }else{
            if($this->_player->hasBlackjack() && $this->_dealer->hasBlackjack()){
                $value = 0;
                ($this->_player->isInsuranced()) ? $value += $this->_player->getInsuranceValue()*2 : $value += 0;
                //$value -= $this->_player->getBetValue();
                $this->_player->_winValue += $value;
                $this->_player->addMoney($value);
                return 'Tie (blackjack) - push '.$value;
            }elseif($this->_player->hasBlackjack() && !$this->_dealer->hasBlackjack()){
                $value = 0;
                ($this->_player->isInsuranced()) ? $value -= $this->_player->getInsuranceValue() : $value += 0;
                $value = $value + (($this->_player->getBetValue()*3)/2);
                $this->_player->_winValue += $value;
                $this->_player->addMoney($value);
                return 'Player won - blackjack '.$value;
            }elseif(!$this->_player->hasBlackjack() && $this->_dealer->hasBlackjack()){
                $value = 0;
                ($this->_player->isInsuranced()) ? $value += $this->_player->getInsuranceValue()*2 : $value += 0;
                $value -= $this->_player->getBetValue();
                $this->_player->_winValue += $value;
                $this->_player->addMoney($value);
                return 'Dealer won - blackjack '.$value;
            }elseif(!$this->_player->hasBlackjack() && !$this->_dealer->hasBlackjack()){
                if($this->_dealer->isBusted()){
                    $value = 0;
                    ($this->_player->isInsuranced()) ? $value -= $this->_player->getInsuranceValue() : $value += 0;
                    $value += $this->_player->getBetValue();
                    $this->_player->_winValue += $value;
                    $this->_player->addMoney($value);
                    return 'Player won - dealer busted '.$value;
                }else{
                    if($this->_player->getHandPoints() > $this->_dealer->getHandPoints()){
                        $value = 0;
                        ($this->_player->isInsuranced()) ? $value -= $this->_player->getInsuranceValue() : $value += 0;
                        $value += $this->_player->getBetValue();
                        $this->_player->_winValue += $value;
                        $this->_player->addMoney($value);
                        return 'Player won - more points '.$value;
                    }elseif($this->_player->getHandPoints() == $this->_dealer->getHandPoints()){
                        $value = 0;
                        ($this->_player->isInsuranced()) ? $value -= $this->_player->getInsuranceValue() : $value += 0;
                        //$value += $this->_player->getBetValue();
                        $this->_player->_winValue += $value;
                        $this->_player->addMoney($value);
                        return 'Tie (points) - push '.$value;
                    }else{
                        $value = 0;
                        ($this->_player->isInsuranced()) ? $value -= $this->_player->getInsuranceValue() : $value += 0;
                        $value -= $this->_player->getBetValue();
                        $this->_player->_winValue += $value;
                        $this->_player->addMoney($value);
                        return 'Dealer won - more points '.$value;
                    }
                }
            }
        }            
    }

    public function possibleActions(){
        switch($this->_state){
            case(State::INVALID):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
            case(State::START):
                $this->_possibleActions = array('betValue' => TRUE, 'deal' => TRUE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
            case(State::OFFER_INSURANCE):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => TRUE, 'rejectInsurance' => TRUE);
                return $this->_possibleActions;
            case(State::PLAYER_ACTION):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => TRUE, 'stand' => TRUE, 'double' => TRUE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
            case(State::PLAYING):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => TRUE, 'stand' => TRUE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
            case(State::DEALER_ACTION):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
                
            case(State::FINALIZE):
            case(State::READY_TO_RESTART):
                $this->_possibleActions = array('betValue' => FALSE, 'deal' => FALSE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
                return $this->_possibleActions;
        }
    }
    
    public function updateState(){
        switch($this->_state){
            case State::INVALID:
                if($this->_dealer instanceof Dealer && $this->_player instanceof Player && !empty($this->_possibleActions))
                    $this->_state = State::START;
                break;
            case State::START:
                if($this->_player->hasBlackjack()){
                    $this->_state = State::FINALIZE;
                }else{
                    if($this->_dealer->getUpCard()->getRank() === Rank::ACE && $this->_player->checkMoney($this->_player->getInitialBetValue()*1.5))
                        $this->_state = State::OFFER_INSURANCE;
                    elseif($this->_player->checkMoney($this->_player->getInitialBetValue()*2))
                        $this->_state = State::PLAYER_ACTION;
                    else
                        $this->_state = State::PLAYING;
                }
                break;
            case State::OFFER_INSURANCE:
                if($this->_player->isInsuranced() !== NULL)
                    $this->_state = State::PLAYER_ACTION;
                break;
            case State::PLAYER_ACTION:
                if($this->_player->getTotals()[0] > 21 || $this->_player->isDouble() === TRUE || $this->_player->isStand() === TRUE)
                    $this->_state = State::DEALER_ACTION;
                elseif($this->_player->isDouble() === FALSE || !$this->_player->checkMoney($this->_player->getInitialBetValue()*2))
                    $this->_state = State::PLAYING;
                break;
            case State::PLAYING:
                if($this->_player->getTotals()[0] > 21 || $this->_player->isStand() === TRUE)
                    $this->_state = State::DEALER_ACTION;
                else
                    $this->_state = State::PLAYING;
                break;
            case State::DEALER_ACTION:
                if($this->_dealer->getTotals()[0] < 17)
                    $this->_state = State::DEALER_ACTION;
                else
                    $this->_state = State::FINALIZE;
                break;
            case State::FINALIZE:
                $this->_state = State::READY_TO_RESTART;                
        }
    }
    
    public function restart(){
        $this->_player->restart();
        $this->_dealer->restart();
        $this->_possibleActions = array('betValue' => TRUE, 'deal' => TRUE, 'hit' => FALSE, 'stand' => FALSE, 'double' => FALSE, 'buyInsurance' => FALSE, 'rejectInsurance' => FALSE);
        $this->_state = State::START;
    }
    
    public function debug(){
        echo '<pre>';
        print_r($this->_dealer);
        print_r($this->_player);
        echo '</br>'.$this->_state.'</br>';
        print_r($this->_possibleActions);
        echo '</pre>';
    }
}