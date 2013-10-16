<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\Transaction
 */
class Transaction
{
	
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $txn_id
     */
    private $txn_id;

    /**
     * @var integer $from_user_id
     */
    private $from_user_id;

    /**
     * @var integer $to_user_id
     */
    private $to_user_id;

    /**
     * @var integer $advert_id
     */
    private $advert_id;

    /**
     * @var string $from_user_email
     */
    private $from_user_email;

    /**
     * @var string $from_user_name
     */
    private $from_user_name;

    /**
     * @var string $to_user_email
     */
    private $to_user_email;

    /**
     * @var string $to_user_name
     */
    private $to_user_name;

    /**
     * @var float $amount
     */
    private $amount;

    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $method
     */
    private $method;

    /**
     * @var string $state
     */
    private $state;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     */
    private $updated_at;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $fromUser;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $toUser;

    /**
     * @var Proton\RigbagBundle\Entity\Advert
     */
    private $advert;

    private static $CURRENCIES	= array(
    		'eur'		=> array(
    				'label'		=> 'EUR',
    				'value'		=> 'eur'
    		),
    		'usd'		=> array(
    				'label'		=> 'USD',
    				'value'		=> 'usd'
    		),
    		'chf'		=> array(
    				'label'		=> 'CHF',
    				'value'		=> 'chf'
    		)
    );
    
    
    public static function getCurrencies() {
    	return self::$CURRENCIES;
    }
    
    public static function getCurrencyByCode( $code ) {
    	return self::$CURRENCIES[$code];
    }
    
    public function getCurrencyLabel() {
    	if( $this->currency ) {
    		return self::$CURRENCIES[strtolower( $this->currency )]['label'];
    	}
    	return '';
    }
    
    public function isIncome( $userId ) {
    	switch( $this->type ) {
    		case 'advert':
    			return false;
    			break;
    		case 'subscription':
    			return false;
    			break;
    		case 'buy':
    			if( $userId == $this->from_user_id ) {
    				return false;
    			} else {
    				return true;
    			}
    			break;
    		case 'freebie':
    			if( $userId == $this->from_user_id ) {
    				return true;
    			} else {
    				return false;
    			}
    			break;
    		default:
    			return false;
    	}
    }
    
    public function getTypeLabel( $userId ) {
    	switch( $this->type ) {
    		case 'advert':
    			return 'Item published';
    		break;
    		case 'subscription':
    			return 'Subscription paid';
    		break;
    		case 'buy':
    			if( $userId == $this->from_user_id ) {
    				return 'Item bought';
    			} else {
    				return 'Item sold';
    			}
    		break;
    		case 'freebie':
    			if( $userId == $this->from_user_id ) {
    				return 'Freebie taken';
    			} else {
    				return 'Freebie given';
    			}
    		break;
    		case 'swap':
    			if( $userId == $this->from_user_id ) {
    				return 'Item swapped';
    			} else {
    				return 'Item swapped';
    			}
    		break;
    	}
    }

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
     * Set txn_id
     *
     * @param string $txnId
     * @return Transaction
     */
    public function setTxnId($txnId)
    {
        $this->txn_id = $txnId;
    
        return $this;
    }

    /**
     * Get txn_id
     *
     * @return string 
     */
    public function getTxnId()
    {
        return $this->txn_id;
    }

    /**
     * Set from_user_id
     *
     * @param integer $fromUserId
     * @return Transaction
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
     * Set to_user_id
     *
     * @param integer $toUserId
     * @return Transaction
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
     * Set advert_id
     *
     * @param integer $advertId
     * @return Transaction
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
     * Set from_user_email
     *
     * @param string $fromUserEmail
     * @return Transaction
     */
    public function setFromUserEmail($fromUserEmail)
    {
        $this->from_user_email = $fromUserEmail;
    
        return $this;
    }

    /**
     * Get from_user_email
     *
     * @return string 
     */
    public function getFromUserEmail()
    {
        return $this->from_user_email;
    }

    /**
     * Set from_user_name
     *
     * @param string $fromUserName
     * @return Transaction
     */
    public function setFromUserName($fromUserName)
    {
        $this->from_user_name = $fromUserName;
    
        return $this;
    }

    /**
     * Get from_user_name
     *
     * @return string 
     */
    public function getFromUserName()
    {
        return $this->from_user_name;
    }

    /**
     * Set to_user_email
     *
     * @param string $toUserEmail
     * @return Transaction
     */
    public function setToUserEmail($toUserEmail)
    {
        $this->to_user_email = $toUserEmail;
    
        return $this;
    }

    /**
     * Get to_user_email
     *
     * @return string 
     */
    public function getToUserEmail()
    {
        return $this->to_user_email;
    }

    /**
     * Set to_user_name
     *
     * @param string $toUserName
     * @return Transaction
     */
    public function setToUserName($toUserName)
    {
        $this->to_user_name = $toUserName;
    
        return $this;
    }

    /**
     * Get to_user_name
     *
     * @return string 
     */
    public function getToUserName()
    {
        return $this->to_user_name;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return Transaction
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
     * @return Transaction
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
     * Set description
     *
     * @param string $description
     * @return Transaction
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
     * Set type
     *
     * @param string $type
     * @return Transaction
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
     * Set method
     *
     * @param string $method
     * @return Transaction
     */
    public function setMethod($method)
    {
        $this->method = $method;
    
        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Transaction
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
     * Set token
     *
     * @param string $token
     * @return Transaction
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Transaction
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
     * @return Transaction
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
     * Set fromUser
     *
     * @param Proton\RigbagBundle\Entity\User $fromUser
     * @return Transaction
     */
    public function setFromUser(\Proton\RigbagBundle\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;
    
        return $this;
    }

    /**
     * Get fromUser
     *
     * @return Proton\RigbagBundle\Entity\User 
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set toUser
     *
     * @param Proton\RigbagBundle\Entity\User $toUser
     * @return Transaction
     */
    public function setToUser(\Proton\RigbagBundle\Entity\User $toUser = null)
    {
        $this->toUser = $toUser;
    
        return $this;
    }

    /**
     * Get toUser
     *
     * @return Proton\RigbagBundle\Entity\User 
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set advert
     *
     * @param Proton\RigbagBundle\Entity\Advert $advert
     * @return Transaction
     */
    public function setAdvert(\Proton\RigbagBundle\Entity\Advert $advert = null)
    {
        $this->advert = $advert;
    
        return $this;
    }

    /**
     * Get advert
     *
     * @return Proton\RigbagBundle\Entity\Advert 
     */
    public function getAdvert()
    {
        return $this->advert;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
    	$this->created_at	= new \DateTime();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
    	$this->updated_at	= new \DateTime();
    }

    //FIXME
    public static function prepareDescription($userSource, $userDestination, $advert, $amount, $currency, $type, $method, $state)
    {
        return 'Transaction for: '.$amount;
    }
}