<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\User
 */
class User
{

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $bio
     */
    private $bio;

    /**
     * @var string $phone
     */
    private $phone;

    /**
     * @var string $post_code
     */
    private $post_code;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $facebook_id
     */
    private $facebook_id;

    /**
     * @var string $twitter_id
     */
    private $twitter_id;

    /**
     * @var string $google_id
     */
    private $google_id;

    /**
     * @var string $profile_picture
     */
    private $profile_picture;

    /**
     * @var string $location
     */
    private $location;

    /**
     * @var string $location_country_code
     */
    private $location_country_code;

    /**
     * @var string $state
     */
    private $state;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     */
    private $updated_at;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $interests;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $options;

    /**
     * @var string $is_main
     */
    private $payment_mode;

    public function hasCircle( $circle ) {

    	if( is_object( $circle ) ) {
    		$circle = $circle->getId();
    	}

    	$has = false;
    	foreach( $this->getCircles() as $c ) {
    		if( $c->getId() == $circle ) {
    			$has = true;
    			break;
    		}
    	}

    	return $has;
    }

    /**
     * 0			- UNKNOW TYPE
     * 1			- OK (FREE)
     * 2			- OK (ANNUAL SUBSCRIBED)
     * 10			- ANNUAL (NEVER PAID)
     * 11			- ANNUAL (EXPIRED)
     */
    public function getAccountState()
    {

    	switch( $this->getAccountType() )
    	{
    		case 'free':
    			return 1;
    		break;
    		case 'annual':
    			if( $this->getExpiredAt() == null ) {
    				return 10;
    			} elseif ( $this->getExpiredAt()->getTimestamp() < time() ) {
    				return 11;
    			}
    			return 2;
    		break;
    	}

    	return 0;
    }

    public function getOptionValue( $key, $defaultValue = null ) {
    	$options		= $this->getOptions();

    	$defaultValue	= null;
    	if( is_null( $options ) || !count( $options ) ) {
    		return $defaultValue;
    	}
    	foreach( $options as $option ) {
    		if( $option->getOptionKey() == $key ) {
    			return $option->getOptionValue();
    		}
    	}

    	return $defaultValue;
    }

    public function getDisplayName() {
    	return $this->getName();
    }

    public static function encodeHash( $userId ) {
    	$tpl	= '000000000';
    	return substr( $tpl, 0, strlen( $userId ) ) . $userId;
    }

    public static function decodeHash( $userHash ) {
    	while( substr( $userHash, 0, 1 ) == '0' ) {
    		$userHash = substr( $userHash, 1 );
    	}
    	return $userHash;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interests = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set bio
     *
     * @param string $bio
     * @return User
     */
    public function setBio($bio)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set post_code
     *
     * @param string $postCode
     * @return User
     */
    public function setPostCode($postCode)
    {
        $this->post_code = $postCode;

        return $this;
    }

    /**
     * Get post_code
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->post_code;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set facebook_id
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebook_id
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set twitter_id
     *
     * @param string $twitterId
     * @return User
     */
    public function setTwitterId($twitterId)
    {
        $this->twitter_id = $twitterId;

        return $this;
    }

    /**
     * Get twitter_id
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    /**
     * Set google_id
     *
     * @param string $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->google_id = $googleId;

        return $this;
    }

    /**
     * Get google_id
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set profile_picture
     *
     * @param string $profilePicture
     * @return User
     */
    public function setProfilePicture($profilePicture)
    {
        $this->profile_picture = $profilePicture;

        return $this;
    }

    /**
     * Get profile_picture
     *
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profile_picture;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return User
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location_country_code
     *
     * @param string $locationCountryCode
     * @return User
     */
    public function setLocationCountryCode($locationCountryCode)
    {
        $this->location_country_code = $locationCountryCode;

        return $this;
    }

    /**
     * Get location_country_code
     *
     * @return string
     */
    public function getLocationCountryCode()
    {
        return $this->location_country_code;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return User
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return User
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
     * @return User
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
     * Add interests
     *
     * @param Proton\RigbagBundle\Entity\Interest $interests
     * @return User
     */
    public function addInterest(\Proton\RigbagBundle\Entity\Interest $interests)
    {
        $this->interests[] = $interests;

        return $this;
    }

    /**
     * Remove interests
     *
     * @param Proton\RigbagBundle\Entity\Interest $interests
     */
    public function removeInterest(\Proton\RigbagBundle\Entity\Interest $interests)
    {
        $this->interests->removeElement($interests);
    }

    /**
     * Get interests
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInterests()
    {
        return $this->interests;
    }

	/**
     * Check for user interest
     *
     * @return boolean
     */
	public function hasInterest(\Proton\RigbagBundle\Entity\Interest $interest) 
	{
		if( is_object( $interest ) ) {
    		$interestId = $interest->getId();
    	}
		
		foreach ($this->interests as $currentInterest) {
			if ($currentInterest->getId() == $interestId) {
				return true;
				break;
			}
		}
		return false;
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
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $adverts;


    /**
     * Add adverts
     *
     * @param Proton\RigbagBundle\Entity\Advert $adverts
     * @return User
     */
    public function addAdvert(\Proton\RigbagBundle\Entity\Advert $adverts)
    {
        $this->adverts[] = $adverts;

        return $this;
    }

    /**
     * Remove adverts
     *
     * @param Proton\RigbagBundle\Entity\Advert $adverts
     */
    public function removeAdvert(\Proton\RigbagBundle\Entity\Advert $adverts)
    {
        $this->adverts->removeElement($adverts);
    }

    /**
     * Get adverts
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAdverts()
    {
        return $this->adverts;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $circles;


    /**
     * Add circles
     *
     * @param Proton\RigbagBundle\Entity\Circle $circles
     * @return User
     */
    public function addCircle(\Proton\RigbagBundle\Entity\Circle $circles)
    {
        $this->circles[] = $circles;

        return $this;
    }

    /**
     * Remove circles
     *
     * @param Proton\RigbagBundle\Entity\Circle $circles
     */
    public function removeCircle(\Proton\RigbagBundle\Entity\Circle $circles)
    {
        $this->circles->removeElement($circles);
    }

    /**
     * Get circles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCircles()
    {
        return $this->circles;
    }
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $questions;


    /**
     * Add questions
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $questions
     * @return User
     */
    public function addQuestion(\Proton\RigbagBundle\Entity\QaPosition $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $questions
     */
    public function removeQuestion(\Proton\RigbagBundle\Entity\QaPosition $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }
    /**
     * @var string $account_type
     */
    private $account_type;

    /**
     * @var \DateTime $expired_at
     */
    private $expired_at;


    /**
     * Set account_type
     *
     * @param string $accountType
     * @return User
     */
    public function setAccountType($accountType)
    {
        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get account_type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }


    /**
     * Set expired_at
     *
     * @param \DateTime $expiredAt
     * @return User
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expired_at = $expiredAt;

        return $this;
    }

    /**
     * Get expired_at
     *
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expired_at;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $answers;


    /**
     * Add answers
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $answers
     * @return User
     */
    public function addAnswer(\Proton\RigbagBundle\Entity\QaPosition $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $answers
     */
    public function removeAnswer(\Proton\RigbagBundle\Entity\QaPosition $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add options
     *
     * @param Proton\RigbagBundle\Entity\UserOption $options
     * @return User
     */
    public function addOption(\Proton\RigbagBundle\Entity\UserOption $options)
    {
        $this->options[] = $options;

        return $this;
    }

    /**
     * Remove options
     *
     * @param Proton\RigbagBundle\Entity\UserOption $options
     */
    public function removeOption(\Proton\RigbagBundle\Entity\UserOption $options)
    {
        $this->options->removeElement($options);
    }

    /**
     * Get options
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * @var string $paypal_id
     */
    private $paypal_id;


    /**
     * Set paypal_id
     *
     * @param string $paypalId
     * @return User
     */
    public function setPaypalId($paypalId)
    {
        $this->paypal_id = $paypalId;

        return $this;
    }

    /**
     * Get paypal_id
     *
     * @return string
     */
    public function getPaypalId()
    {
        return $this->paypal_id;
    }
    /**
     * @var string $location_formated
     */
    private $location_formated;

    /**
     * @var float $location_lat
     */
    private $location_lat;

    /**
     * @var float $location_lng
     */
    private $location_lng;


    /**
     * Set location_formated
     *
     * @param string $locationFormated
     * @return User
     */
    public function setLocationFormated($locationFormated)
    {
        $this->location_formated = $locationFormated;

        return $this;
    }

    /**
     * Get location_formated
     *
     * @return string
     */
    public function getLocationFormated()
    {
        return $this->location_formated;
    }

    /**
     * Set location_lat
     *
     * @param float $locationLat
     * @return User
     */
    public function setLocationLat($locationLat)
    {
        $this->location_lat = $locationLat;

        return $this;
    }

    /**
     * Get location_lat
     *
     * @return float
     */
    public function getLocationLat()
    {
        return $this->location_lat;
    }

    /**
     * Set location_lng
     *
     * @param float $locationLng
     * @return User
     */
    public function setLocationLng($locationLng)
    {
        $this->location_lng = $locationLng;

        return $this;
    }

    /**
     * Get location_lng
     *
     * @return float
     */
    public function getLocationLng()
    {
        return $this->location_lng;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $facebookObjects;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $createdFacebookObjects;


    /**
     * Add facebookObjects
     *
     * @param Proton\RigbagBundle\Entity\FacebookObject $facebookObjects
     * @return User
     */
    public function addFacebookObject(\Proton\RigbagBundle\Entity\FacebookObject $facebookObjects)
    {
        $this->facebookObjects[] = $facebookObjects;

        return $this;
    }

    /**
     * Remove facebookObjects
     *
     * @param Proton\RigbagBundle\Entity\FacebookObject $facebookObjects
     */
    public function removeFacebookObject(\Proton\RigbagBundle\Entity\FacebookObject $facebookObjects)
    {
        $this->facebookObjects->removeElement($facebookObjects);
    }

    /**
     * Get facebookObjects
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFacebookObjects()
    {
        return $this->facebookObjects;
    }

    /**
     * Add createdFacebookObjects
     *
     * @param Proton\RigbagBundle\Entity\FacebookObject $createdFacebookObjects
     * @return User
     */
    public function addCreatedFacebookObject(\Proton\RigbagBundle\Entity\FacebookObject $createdFacebookObjects)
    {
        $this->createdFacebookObjects[] = $createdFacebookObjects;

        return $this;
    }

    /**
     * Remove createdFacebookObjects
     *
     * @param Proton\RigbagBundle\Entity\FacebookObject $createdFacebookObjects
     */
    public function removeCreatedFacebookObject(\Proton\RigbagBundle\Entity\FacebookObject $createdFacebookObjects)
    {
        $this->createdFacebookObjects->removeElement($createdFacebookObjects);
    }

    /**
     * Get createdFacebookObjects
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCreatedFacebookObjects()
    {
        return $this->createdFacebookObjects;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $facebookActions;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $createdFacebookActions;


    /**
     * Add facebookActions
     *
     * @param Proton\RigbagBundle\Entity\FacebookAction $facebookActions
     * @return User
     */
    public function addFacebookAction(\Proton\RigbagBundle\Entity\FacebookAction $facebookActions)
    {
        $this->facebookActions[] = $facebookActions;

        return $this;
    }

    /**
     * Remove facebookActions
     *
     * @param Proton\RigbagBundle\Entity\FacebookAction $facebookActions
     */
    public function removeFacebookAction(\Proton\RigbagBundle\Entity\FacebookAction $facebookActions)
    {
        $this->facebookActions->removeElement($facebookActions);
    }

    /**
     * Get facebookActions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFacebookActions()
    {
        return $this->facebookActions;
    }

    /**
     * Add createdFacebookActions
     *
     * @param Proton\RigbagBundle\Entity\FacebookAction $createdFacebookActions
     * @return User
     */
    public function addCreatedFacebookAction(\Proton\RigbagBundle\Entity\FacebookAction $createdFacebookActions)
    {
        $this->createdFacebookActions[] = $createdFacebookActions;

        return $this;
    }

    /**
     * Remove createdFacebookActions
     *
     * @param Proton\RigbagBundle\Entity\FacebookAction $createdFacebookActions
     */
    public function removeCreatedFacebookAction(\Proton\RigbagBundle\Entity\FacebookAction $createdFacebookActions)
    {
        $this->createdFacebookActions->removeElement($createdFacebookActions);
    }

    /**
     * Get createdFacebookActions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCreatedFacebookActions()
    {
        return $this->createdFacebookActions;
    }
    /**
     * @var string $facebook_token
     */
    private $facebook_token;

    /**
     * @var string $google_token
     */
    private $google_token;


    /**
     * Set facebook_token
     *
     * @param string $facebookToken
     * @return User
     */
    public function setFacebookToken($facebookToken)
    {
        $this->facebook_token = $facebookToken;

        return $this;
    }

    /**
     * Get facebook_token
     *
     * @return string
     */
    public function getFacebookToken()
    {
        return $this->facebook_token;
    }

    /**
     * Set google_token
     *
     * @param string $googleToken
     * @return User
     */
    public function setGoogleToken($googleToken)
    {
        $this->google_token = $googleToken;

        return $this;
    }

    /**
     * Get google_token
     *
     * @return string
     */
    public function getGoogleToken()
    {
        return $this->google_token;
    }
    /**
     * @var string $twitter_token
     */
    private $twitter_token;


    /**
     * Set twitter_token
     *
     * @param string $twitterToken
     * @return User
     */
    public function setTwitterToken($twitterToken)
    {
        $this->twitter_token = serialize( $twitterToken );

        return $this;
    }

    /**
     * Get twitter_token
     *
     * @return string
     */
    public function getTwitterToken()
    {
        return unserialize( $this->twitter_token );
    }
    
    /**
     * Set is_main
     *
     * @param string $paymentMode
     * @return PaymentMode
     */
    public function setPaymentMode($paymentMode)
    {
        $this->payment_mode = $paymentMode;
    
        return $this;
    }

    /**
     * Get payment_mode
     *
     * @return string 
     */
    public function getPaymentMode()
    {
        return $this->payment_mode;
    }
}