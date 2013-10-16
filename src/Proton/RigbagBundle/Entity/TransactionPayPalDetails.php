<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\TransactionPayPalDetails
 */
class TransactionPayPalDetails
{
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $transaction_id
     */
    private $transaction_id;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $verify_sign
     */
    private $verify_sign;

    /**
     * @var string $notify_version
     */
    private $notify_version;

    /**
     * @var integer $parent_id
     */
    private $parent_id;

    /**
     * @var string $receipt_id
     */
    private $receipt_id;

    /**
     * @var string $receiver_id
     */
    private $receiver_id;

    /**
     * @var string $receiver_email
     */
    private $receiver_email;

    /**
     * @var string $receiver_name
     */
    private $receiver_name;

    /**
     * @var integer $resend
     */
    private $resend;

    /**
     * @var string $sender_id
     */
    private $sender_id;

    /**
     * @var string $sender_email
     */
    private $sender_email;

    /**
     * @var string $sender_buisness
     */
    private $sender_buisness;

    /**
     * @var string $sender_name
     */
    private $sender_name;

    /**
     * @var string $sender_phone
     */
    private $sender_phone;

    /**
     * @var string $sender_address_status
     */
    private $sender_address_status;

    /**
     * @var string $sender_country_code
     */
    private $sender_country_code;

    /**
     * @var string $sender_country
     */
    private $sender_country;

    /**
     * @var string $sender_city
     */
    private $sender_city;

    /**
     * @var string $sender_street
     */
    private $sender_street;

    /**
     * @var string $sender_zip
     */
    private $sender_zip;

    /**
     * @var string $authorization_status
     */
    private $authorization_status;

    /**
     * @var string $exchange_rate
     */
    private $exchange_rate;

    /**
     * @var string $payment_status
     */
    private $payment_status;

    /**
     * @var string $payment_type
     */
    private $payment_type;

    /**
     * @var string $pending_reason
     */
    private $pending_reason;

    /**
     * @var string $mc_currency
     */
    private $mc_currency;

    /**
     * @var string $mc_gross
     */
    private $mc_gross;

    /**
     * @var string $memo
     */
    private $memo;

    /**
     * @var string $tracking_id
     */
    private $tracking_id;

    /**
     * @var string $reasone_code
     */
    private $reasone_code;

    /**
     * @var Proton\RigbagBundle\Entity\Transaction
     */
    private $transaction;


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
     * Set transaction_id
     *
     * @param integer $transactionId
     * @return TransactionPayPalDetails
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;
    
        return $this;
    }

    /**
     * Get transaction_id
     *
     * @return integer 
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return TransactionPayPalDetails
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
     * Set type
     *
     * @param string $type
     * @return TransactionPayPalDetails
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
     * Set verify_sign
     *
     * @param string $verifySign
     * @return TransactionPayPalDetails
     */
    public function setVerifySign($verifySign)
    {
        $this->verify_sign = $verifySign;
    
        return $this;
    }

    /**
     * Get verify_sign
     *
     * @return string 
     */
    public function getVerifySign()
    {
        return $this->verify_sign;
    }

    /**
     * Set notify_version
     *
     * @param string $notifyVersion
     * @return TransactionPayPalDetails
     */
    public function setNotifyVersion($notifyVersion)
    {
        $this->notify_version = $notifyVersion;
    
        return $this;
    }

    /**
     * Get notify_version
     *
     * @return string 
     */
    public function getNotifyVersion()
    {
        return $this->notify_version;
    }

    /**
     * Set parent_id
     *
     * @param integer $parentId
     * @return TransactionPayPalDetails
     */
    public function setParentId($parentId)
    {
        $this->parent_id = $parentId;
    
        return $this;
    }

    /**
     * Get parent_id
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Set receipt_id
     *
     * @param string $receiptId
     * @return TransactionPayPalDetails
     */
    public function setReceiptId($receiptId)
    {
        $this->receipt_id = $receiptId;
    
        return $this;
    }

    /**
     * Get receipt_id
     *
     * @return string 
     */
    public function getReceiptId()
    {
        return $this->receipt_id;
    }

    /**
     * Set receiver_id
     *
     * @param string $receiverId
     * @return TransactionPayPalDetails
     */
    public function setReceiverId($receiverId)
    {
        $this->receiver_id = $receiverId;
    
        return $this;
    }

    /**
     * Get receiver_id
     *
     * @return string 
     */
    public function getReceiverId()
    {
        return $this->receiver_id;
    }

    /**
     * Set receiver_email
     *
     * @param string $receiverEmail
     * @return TransactionPayPalDetails
     */
    public function setReceiverEmail($receiverEmail)
    {
        $this->receiver_email = $receiverEmail;
    
        return $this;
    }

    /**
     * Get receiver_email
     *
     * @return string 
     */
    public function getReceiverEmail()
    {
        return $this->receiver_email;
    }

    /**
     * Set receiver_name
     *
     * @param string $receiverName
     * @return TransactionPayPalDetails
     */
    public function setReceiverName($receiverName)
    {
        $this->receiver_name = $receiverName;
    
        return $this;
    }

    /**
     * Get receiver_name
     *
     * @return string 
     */
    public function getReceiverName()
    {
        return $this->receiver_name;
    }

    /**
     * Set resend
     *
     * @param integer $resend
     * @return TransactionPayPalDetails
     */
    public function setResend($resend)
    {
        $this->resend = $resend;
    
        return $this;
    }

    /**
     * Get resend
     *
     * @return integer 
     */
    public function getResend()
    {
        return $this->resend;
    }

    /**
     * Set sender_id
     *
     * @param string $senderId
     * @return TransactionPayPalDetails
     */
    public function setSenderId($senderId)
    {
        $this->sender_id = $senderId;
    
        return $this;
    }

    /**
     * Get sender_id
     *
     * @return string 
     */
    public function getSenderId()
    {
        return $this->sender_id;
    }

    /**
     * Set sender_email
     *
     * @param string $senderEmail
     * @return TransactionPayPalDetails
     */
    public function setSenderEmail($senderEmail)
    {
        $this->sender_email = $senderEmail;
    
        return $this;
    }

    /**
     * Get sender_email
     *
     * @return string 
     */
    public function getSenderEmail()
    {
        return $this->sender_email;
    }

    /**
     * Set sender_buisness
     *
     * @param string $senderBuisness
     * @return TransactionPayPalDetails
     */
    public function setSenderBuisness($senderBuisness)
    {
        $this->sender_buisness = $senderBuisness;
    
        return $this;
    }

    /**
     * Get sender_buisness
     *
     * @return string 
     */
    public function getSenderBuisness()
    {
        return $this->sender_buisness;
    }

    /**
     * Set sender_name
     *
     * @param string $senderName
     * @return TransactionPayPalDetails
     */
    public function setSenderName($senderName)
    {
        $this->sender_name = $senderName;
    
        return $this;
    }

    /**
     * Get sender_name
     *
     * @return string 
     */
    public function getSenderName()
    {
        return $this->sender_name;
    }

    /**
     * Set sender_phone
     *
     * @param string $senderPhone
     * @return TransactionPayPalDetails
     */
    public function setSenderPhone($senderPhone)
    {
        $this->sender_phone = $senderPhone;
    
        return $this;
    }

    /**
     * Get sender_phone
     *
     * @return string 
     */
    public function getSenderPhone()
    {
        return $this->sender_phone;
    }

    /**
     * Set sender_address_status
     *
     * @param string $senderAddressStatus
     * @return TransactionPayPalDetails
     */
    public function setSenderAddressStatus($senderAddressStatus)
    {
        $this->sender_address_status = $senderAddressStatus;
    
        return $this;
    }

    /**
     * Get sender_address_status
     *
     * @return string 
     */
    public function getSenderAddressStatus()
    {
        return $this->sender_address_status;
    }

    /**
     * Set sender_country_code
     *
     * @param string $senderCountryCode
     * @return TransactionPayPalDetails
     */
    public function setSenderCountryCode($senderCountryCode)
    {
        $this->sender_country_code = $senderCountryCode;
    
        return $this;
    }

    /**
     * Get sender_country_code
     *
     * @return string 
     */
    public function getSenderCountryCode()
    {
        return $this->sender_country_code;
    }

    /**
     * Set sender_country
     *
     * @param string $senderCountry
     * @return TransactionPayPalDetails
     */
    public function setSenderCountry($senderCountry)
    {
        $this->sender_country = $senderCountry;
    
        return $this;
    }

    /**
     * Get sender_country
     *
     * @return string 
     */
    public function getSenderCountry()
    {
        return $this->sender_country;
    }

    /**
     * Set sender_city
     *
     * @param string $senderCity
     * @return TransactionPayPalDetails
     */
    public function setSenderCity($senderCity)
    {
        $this->sender_city = $senderCity;
    
        return $this;
    }

    /**
     * Get sender_city
     *
     * @return string 
     */
    public function getSenderCity()
    {
        return $this->sender_city;
    }

    /**
     * Set sender_street
     *
     * @param string $senderStreet
     * @return TransactionPayPalDetails
     */
    public function setSenderStreet($senderStreet)
    {
        $this->sender_street = $senderStreet;
    
        return $this;
    }

    /**
     * Get sender_street
     *
     * @return string 
     */
    public function getSenderStreet()
    {
        return $this->sender_street;
    }

    /**
     * Set sender_zip
     *
     * @param string $senderZip
     * @return TransactionPayPalDetails
     */
    public function setSenderZip($senderZip)
    {
        $this->sender_zip = $senderZip;
    
        return $this;
    }

    /**
     * Get sender_zip
     *
     * @return string 
     */
    public function getSenderZip()
    {
        return $this->sender_zip;
    }

    /**
     * Set authorization_status
     *
     * @param string $authorizationStatus
     * @return TransactionPayPalDetails
     */
    public function setAuthorizationStatus($authorizationStatus)
    {
        $this->authorization_status = $authorizationStatus;
    
        return $this;
    }

    /**
     * Get authorization_status
     *
     * @return string 
     */
    public function getAuthorizationStatus()
    {
        return $this->authorization_status;
    }

    /**
     * Set exchange_rate
     *
     * @param string $exchangeRate
     * @return TransactionPayPalDetails
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchange_rate = $exchangeRate;
    
        return $this;
    }

    /**
     * Get exchange_rate
     *
     * @return string 
     */
    public function getExchangeRate()
    {
        return $this->exchange_rate;
    }

    /**
     * Set payment_status
     *
     * @param string $paymentStatus
     * @return TransactionPayPalDetails
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->payment_status = $paymentStatus;
    
        return $this;
    }

    /**
     * Get payment_status
     *
     * @return string 
     */
    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    /**
     * Set payment_type
     *
     * @param string $paymentType
     * @return TransactionPayPalDetails
     */
    public function setPaymentType($paymentType)
    {
        $this->payment_type = $paymentType;
    
        return $this;
    }

    /**
     * Get payment_type
     *
     * @return string 
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Set pending_reason
     *
     * @param string $pendingReason
     * @return TransactionPayPalDetails
     */
    public function setPendingReason($pendingReason)
    {
        $this->pending_reason = $pendingReason;
    
        return $this;
    }

    /**
     * Get pending_reason
     *
     * @return string 
     */
    public function getPendingReason()
    {
        return $this->pending_reason;
    }

    /**
     * Set mc_currency
     *
     * @param string $mcCurrency
     * @return TransactionPayPalDetails
     */
    public function setMcCurrency($mcCurrency)
    {
        $this->mc_currency = $mcCurrency;
    
        return $this;
    }

    /**
     * Get mc_currency
     *
     * @return string 
     */
    public function getMcCurrency()
    {
        return $this->mc_currency;
    }

    /**
     * Set mc_gross
     *
     * @param string $mcGross
     * @return TransactionPayPalDetails
     */
    public function setMcGross($mcGross)
    {
        $this->mc_gross = $mcGross;
    
        return $this;
    }

    /**
     * Get mc_gross
     *
     * @return string 
     */
    public function getMcGross()
    {
        return $this->mc_gross;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return TransactionPayPalDetails
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
    
        return $this;
    }

    /**
     * Get memo
     *
     * @return string 
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * Set tracking_id
     *
     * @param string $trackingId
     * @return TransactionPayPalDetails
     */
    public function setTrackingId($trackingId)
    {
        $this->tracking_id = $trackingId;
    
        return $this;
    }

    /**
     * Get tracking_id
     *
     * @return string 
     */
    public function getTrackingId()
    {
        return $this->tracking_id;
    }

    /**
     * Set reasone_code
     *
     * @param string $reasoneCode
     * @return TransactionPayPalDetails
     */
    public function setReasoneCode($reasoneCode)
    {
        $this->reasone_code = $reasoneCode;
    
        return $this;
    }

    /**
     * Get reasone_code
     *
     * @return string 
     */
    public function getReasoneCode()
    {
        return $this->reasone_code;
    }

    /**
     * Set transaction
     *
     * @param Proton\RigbagBundle\Entity\Transaction $transaction
     * @return TransactionPayPalDetails
     */
    public function setTransaction(\Proton\RigbagBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;
    
        return $this;
    }

    /**
     * Get transaction
     *
     * @return Proton\RigbagBundle\Entity\Transaction 
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
     /**
	 * @ORM\PrePersist
	 */
	public function setCreatedAtValue()
	{
		$this->created_at	= new \DateTime();
	}
}