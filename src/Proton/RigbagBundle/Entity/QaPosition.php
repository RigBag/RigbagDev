<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\QaPosition
 */
class QaPosition
{
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $user_id
     */
    private $user_id;

    /**
     * @var integer $advert_id
     */
    private $advert_id;

    /**
     * @var integer $circle_id
     */
    private $circle_id;

    /**
     * @var integer $parent_id
     */
    private $parent_id;

    /**
     * @var string $body
     */
    private $body;

    /**
     * @var integer $answers_num
     */
    private $answers_num;

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
    private $answers;

    /**
     * @var Proton\RigbagBundle\Entity\QaPosition
     */
    private $question;

    /**
     * @var Proton\RigbagBundle\Entity\Advert
     */
    private $advert;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $user;

    /**
     * @var Proton\RigbagBundle\Entity\Circle
     */
    private $circle;

    
    public function getAddedAgo() {
    
    
    	$date	= strtotime( $this->created_at->format('Y-m-d H:i:s') );
    	$now	= time();
    	$time	= $now - $date;
    
    	$days		= floor( $time / ( 60 * 60 * 24 ) );
    	$hours		= 0;
    	$minutes	= 0;
    
    	if( $days < 1 ) {
    
    		$rest	= $time - ( $days * 60 * 60 * 24 );
    
    		$hours	= floor( $rest / ( 60 * 60 ) );
    
    		if( $hours < 1 ) {
    			$rest	= $time - ( $days * 60 * 60 );
    
    			$minutes	= floor( $rest / 60 );
    		}
    	}
    
    	return	array(
    			'days'		=> $days,
    			'minutes'	=> $minutes,
    			'hours'		=> $hours
    	);
    }
    
    public function getUpdatedAgo() {
    
    
    	$date	= strtotime( $this->updated_at->format('Y-m-d H:i:s') );
    	$now	= time();
    	$time	= $now - $date;
    
    	$days		= floor( $time / ( 60 * 60 * 24 ) );
    	$hours		= 0;
    	$minutes	= 0;
    
    	if( $days < 1 ) {
    
    		$rest	= $time - ( $days * 60 * 60 * 24 );
    
    		$hours	= floor( $rest / ( 60 * 60 ) );
    
    		if( $hours < 1 ) {
    			$rest	= $time - ( $days * 60 * 60 );
    
    			$minutes	= floor( $rest / 60 );
    		}
    	}
    
    	return	array(
    			'days'		=> $days,
    			'minutes'	=> $minutes,
    			'hours'		=> $hours
    	);
    }
    
    public static function encodeHash( $qaId ) {
    	$tpl	= '000000000';
    	return substr( $tpl, 0, strlen( $qaId ) ) . $qaId;
    }
    
    public static function decodeHash( $qaHash ) {
    	while( substr( $qaHash, 0, 1 ) == '0' ) {
    		$qaHash = substr( $qaHash, 1 );
    	}
    	return $qaHash;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set user_id
     *
     * @param integer $userId
     * @return QaPosition
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set advert_id
     *
     * @param integer $advertId
     * @return QaPosition
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
     * Set circle_id
     *
     * @param integer $circleId
     * @return QaPosition
     */
    public function setCircleId($circleId)
    {
        $this->circle_id = $circleId;
    
        return $this;
    }

    /**
     * Get circle_id
     *
     * @return integer 
     */
    public function getCircleId()
    {
        return $this->circle_id;
    }

    /**
     * Set parent_id
     *
     * @param integer $parentId
     * @return QaPosition
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
     * Set body
     *
     * @param string $body
     * @return QaPosition
     */
    public function setBody($body)
    {
        $this->body = $body;
    
        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set answers_num
     *
     * @param integer $answersNum
     * @return QaPosition
     */
    public function setAnswersNum($answersNum)
    {
        $this->answers_num = $answersNum;
    
        return $this;
    }

    /**
     * Get answers_num
     *
     * @return integer 
     */
    public function getAnswersNum()
    {
        return $this->answers_num;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return QaPosition
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
     * @return QaPosition
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
     * Add answers
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $answers
     * @return QaPosition
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
     * Set question
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $question
     * @return QaPosition
     */
    public function setQuestion(\Proton\RigbagBundle\Entity\QaPosition $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return Proton\RigbagBundle\Entity\QaPosition 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set advert
     *
     * @param Proton\RigbagBundle\Entity\Advert $advert
     * @return QaPosition
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
     * Set user
     *
     * @param Proton\RigbagBundle\Entity\User $user
     * @return QaPosition
     */
    public function setUser(\Proton\RigbagBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return Proton\RigbagBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set circle
     *
     * @param Proton\RigbagBundle\Entity\Circle $circle
     * @return QaPosition
     */
    public function setCircle(\Proton\RigbagBundle\Entity\Circle $circle = null)
    {
        $this->circle = $circle;
    
        return $this;
    }

    /**
     * Get circle
     *
     * @return Proton\RigbagBundle\Entity\Circle 
     */
    public function getCircle()
    {
        return $this->circle;
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
     * @var string $state
     */
    private $state;


    /**
     * Set state
     *
     * @param string $state
     * @return QaPosition
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
     * @var integer $to_user_id
     */
    private $to_user_id;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $toUser;


    /**
     * Set to_user_id
     *
     * @param integer $toUserId
     * @return QaPosition
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
     * Set toUser
     *
     * @param Proton\RigbagBundle\Entity\User $toUser
     * @return QaPosition
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
     * @var integer $readed
     */
    private $readed;


    /**
     * Set readed
     *
     * @param integer $readed
     * @return QaPosition
     */
    public function setReaded($readed)
    {
        $this->readed = $readed;
    
        return $this;
    }

    /**
     * Get readed
     *
     * @return integer 
     */
    public function getReaded()
    {
        return $this->readed;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $facebookObjects;


    /**
     * Add facebookObjects
     *
     * @param Proton\RigbagBundle\Entity\FacebookObject $facebookObjects
     * @return QaPosition
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
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $facebookActions;


    /**
     * Add facebookActions
     *
     * @param Proton\RigbagBundle\Entity\FacebookAction $facebookActions
     * @return QaPosition
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
}