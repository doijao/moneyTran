<?php
declare(strict_types=1);
namespace App\Controller;

class Transaction
{
    /**
     * 3 currencies are supported: EUR, USD and JPY
     */
    private $currencies = array('EUR' => 1
                                ,'USD' => 1.1497
                                ,'JPY' => 129.53);
    /**
     * Maximum transaction per week. 
     * There will be charges on the future transaction
     */
    private $maxUserTransPerWeek = 3;

    /**
     * Maximum promo amount.
     * If exceeded, commission fee will be charge
     */
    private $maxAmountPerWeek = 1000; 

    /**
     * Promo : no commission fee for the first 3 transaction
     */
    private $promoFee = 0; 

    /**
    * Commission fee - 0.03% from total amount, but no more than 5.00 EUR.
    */ 
    private $cashInFee = 0.0003; 

    /**
     * Maximum commission fee charge for Cash-In
     */
    private $maxCashInFee = 5.00;

    /**
     * Minimum commission fee charge for Legal type
     */
    private $minLegalFee = 0.50;

    /**
    * Default commission fee - 0.3% from cash out amount.
    * 1000.00 EUR per week (from monday to sunday) is free of charge.
    */    
    private $cashOutNaturalFee = 0.003;

    /**
     * Commission fee - 0.3% from amount, but not less than 0.50 EUR for operation.
     */
    private $cashOutLegalFee = 0.003;

    private $error = 'Terminated : ';
    private $odate;
    private $uid;
    private $utype;
    private $otype;
    private $oamount;
    private $ocurrency;
    private $totalAmount = 0.00;
    private $log;
    private $results;

    public function __construct(array $transactions)
    {          
        
        foreach($transactions as $transaction)
        {
            $this->odate = $transaction[0];
            $this->uid = $transaction[1];
            $this->utype = $transaction[2];
            $this->otype = $transaction[3];
            $this->oamount = $transaction[4];
            $this->ocurrency = $transaction[5];               
        
            $commissionFee = null;

            if($this->otype == 'cash_in')
            {
                $commissionFee = $this->computeCashInFee();
            }
            elseif($this->otype == 'cash_out')
            {
                $commissionFee = $this->computeCashOutFee();
            }

            $test = is_float($commissionFee + 0);

            if($test > 0)
            {
                $price = round($commissionFee, 3);            
                $this->results[] = number_format((float)$price, 2, '.', '');                    
                //die(  round( $this->results[0], 10));
            }            
            else
            {               
               $this->results[] = $commissionFee;
            }
        }
     
    }


    public function getResults() : array
    {        
        return $this->results;        
    }


    private function setLog() : void
    {     
        $this->log[$this->uid][] = array (
            'odate'     =>  $this->odate,            
            'utype'     =>  $this->utype,
            'otype'     =>  $this->otype,
            'oamount'   =>  $this->toEuro($this->ocurrency, $this->oamount),
            'ocurrency' =>  $this->ocurrency);
    }


    private function isThesameWeek( string $day1, string $day2) : bool
    {
        $d1 = strtotime($day1);
        $d2 = strtotime($day2);

        $d1w = date("W", $d1);
        $d2w = date("W", $d2);

        $datediff = round(($d1 - $d2) / (60 * 60 * 24));
       
        if($datediff < -7)
            return false;

        return $d1w === $d2w;
    }


    private function toEuro($currency, $amount) : float
    {
        if(!$this->currencies[$currency])        
            die($this->error . 'Currency not supported.');        

        return ($amount / $this->currencies[$currency]);
    }


    private function fromEuro($currency, $amount) : float
    {
        if(!$this->currencies[$currency])        
            die($this->error . 'Currency not supported.');        

        return ($amount * $this->currencies[$currency]);
    }

    
    private function computeCashInFee() : float
    {
        $fee = $this->oamount * $this->cashInFee;

        if($this->toEuro($this->ocurrency, $fee) > $this->maxCashInFee)        
            $fee = $this->maxCashInFee;
        
        return $fee;
    }

    private function computeCashOutFee()
    {        
        if($this->utype == 'natural')
        {
            $count=0;
            $this->totalAmount=0;
            
            // Meaning that user have prior transaction                      
            if(!empty($this->log[$this->uid]))
            {                        
                foreach($this->log[$this->uid] as $userLog)
                {                    
                    if($this->isThesameWeek($userLog['odate'], $this->odate))
                    {                     
                        $this->totalAmount += $userLog['oamount'];
                        $count++;
                    }
                }
            }
            
            if($count > $this->maxUserTransPerWeek)
            {
                $fee = $this->oamount * $this->cashOutNaturalFee;    
            }
            else
            {
               
                if($this->toEuro($this->ocurrency, $this->totalAmount) > $this->maxAmountPerWeek)
                {
                    $fee = $this->oamount * $this->cashOutNaturalFee;
                }
                else
                {
                    if(($this->toEuro($this->ocurrency, $this->totalAmount) + $this->toEuro($this->ocurrency, $this->oamount)) > $this->maxAmountPerWeek)
                    {
                        $convertedToEUR = ($this->toEuro($this->ocurrency, $this->totalAmount) + $this->toEuro($this->ocurrency, $this->oamount) - $this->maxAmountPerWeek);
                        $fee =  $this->fromEuro($this->ocurrency, $convertedToEUR) * $this->cashOutNaturalFee;
                    }
                    else
                    {     
                        
                        $test = is_float($this->oamount + 0);

                        if($test > 0)
                        {   
                            $fee =  number_format($this->oamount * $this->promoFee, 2);                         
                            
                            
                        }
                        else
                        {                            
                            $fee =  $this->oamount * $this->promoFee;
                        }
                        
                    }
                }

            }

        }

        if($this->utype == 'legal')
        {
            $fee = $this->oamount * $this->cashOutLegalFee;            
            if($this->toEuro($this->ocurrency, $fee) < $this->minLegalFee)
            {
                $fee = $this->minLegalFee;
            }    
        }

        $this->setLog();
        
        return $fee;

    }

}