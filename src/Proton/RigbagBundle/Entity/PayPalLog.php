<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\PayPalLog
 */
class PayPalLog
{
   
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $from_user_id
     */
    private $from_user_id;

    /**
     * @var string $from_paypal_id
     */
    private $from_paypal_id;

    /**
     * @var integer $to_user_id
     */
    private $to_user_id;

    /**
     * @var string $to_paypal_id
     */
    private $to_paypal_id;

    /**
     * @var string $payer_id
     */
    private $payer_id;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var float $amount
     */
    private $amount;

    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var string $state
     */
    private $state;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var integer $advert_id
     */
    private $advert_id;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var string $transaction_id
     */
    private $transaction_id;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     */
    private $updated_at;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set from_user_id
     *
     * @param integer $fromUserId
     * @return PayPalLog
     */
    public function setFromUserId($fromUserId)
    {
        $this->from_user_id = $fromUserId;
    
        return $this;
    }

    /**
     * Get from_user_id
     *
     * @return integer 
     */
    public function getFromUserId()
    {
        return $this->from_user_id;
    }

    /**
     * Set from_paypal_id
     *
     * @param string $fromPaypalId
     * @return PayPalLog
     */
    public function setFromPaypalId($fromPaypalId)
    {
        $this->from_paypal_id = $fromPaypalId;
    
        return $this;
    }

    /**
     * Get from_paypal_id
     *
     * @return string 
     */
    public function getFromPaypalId()
    {
        return $this->from_paypal_id;
    }

    /**
     * Set to_user_id
     *
     * @param integer $toUserId
     * @return PayPalLog
     */
    public function setToUserId($toUserId)
    {
        $this->to_user_id = $toUserId;
    
        return $this;
    }

    /**
     * Get to_user_id
     *
     * @return integer 
     */
    public function getToUserId()
    {
        return $this->to_user_id;
    }

    /**
     * Set to_paypal_id
     *
     * @param string $toPaypalId
     * @return PayPalLog
     */
    public function setToPaypalId($toPaypalId)
    {
        $this->to_paypal_id = $toPaypalId;
    
        return $this;
    }

    /**
     * Get to_paypal_id
     *
     * @return string 
     */
    public function getToPaypalId()
    {
        return $this->to_paypal_id;
    }

    /**
     * Set payer_id
     *
     * @param string $payerId
     * @return PayPalLog
     */
    public function setPayerId($payerId)
    {
        $this->payer_id = $payerId;
    
        return $this;
    }

    /**
     * Get payer_id
     *
     * @return string 
     */
    public function getPayerId()
    {
        return $this->payer_id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return PayPalLog
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return PayPalLog
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return PayPalLog
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return PayPalLog
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PayPalLog
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set advert_id
     *
     * @param integer $advertId
     * @return PayPalLog
     */
    public function setAdvertId($advertId)
    {
        $this->advert_id = $advertId;
    
        return $this;
    }

    /**
     * Get advert_id
     *
     * @return integer 
     */
    public function getAdvertId()
    {
        return $this->advert_id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return PayPalLog
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set transaction_id
     *
     * @param string $transactionId
     * @return PayPalLog
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;
    
        return $this;
    }

    /**
     * Get transaction_id
     *
     * @return string 
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return PayPalLog
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return PayPalLog
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        // Add your code here
    }

    /**
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        // Add your code here
    }
}