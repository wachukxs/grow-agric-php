<?php

class Farmm {
    // can do withour these
    // public $farmcountylocation;
    // public $farmsubcountylocation;
    // public $farmwardlocation;
    // public $yearsfarming;
    // public $numberofemployees;
    // public $haveinsurance;
    // public $insurer;
    // public $farmeditems;
    // public $otherfarmeditems;
    // public $challengesfaced;
    // public $otherchallengesfaced;
    // public $multiplechickenhouses;
    // public $id;
    // public $farmerid;
    // public $deleted;
    
    public function __construct()
    {
        if ($this->farmcountylocation) {
            $this->farmcountylocation = htmlspecialchars_decode($this->farmcountylocation, ENT_QUOTES);
        }
        if ($this->farmsubcountylocation) {
            $this->farmsubcountylocation = htmlspecialchars_decode($this->farmsubcountylocation, ENT_QUOTES);
        }
        if ($this->farmwardlocation) {
            $this->farmwardlocation = htmlspecialchars_decode($this->farmwardlocation, ENT_QUOTES);
        }
    }
}


?>