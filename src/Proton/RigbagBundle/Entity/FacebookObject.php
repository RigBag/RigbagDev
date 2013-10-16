<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\FacebookObject
 */
class FacebookObject
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $facebook_id
     */
    private $facebook_id;

    /**
     * @var integer $from_app
     */
    private $from_app;

    /**
     * @var integer $created_by
     */
    private $created_by;

    /**
     * @var integer $advert_id
     */
    private $advert_id;

    /**
     * @var integer $user_id
     */
    private $user_id;

    /**
     * @var integer $qa_position_id
     */
    private $qa_position_id;

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
    private $createdBy;

    /**
     * @var Proton\RigbagBundle\Entity\Advert
     */
    private $advert;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $user;

    /**
     * @var Proton\RigbagBundle\Entity\QaPosition
     */
    private $qaPosition;


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
     * Set type
     *
     * @param string $type
     * @return FacebookObject
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
     * Set facebook_id
     *
     * @param string $facebookId
     * @return FacebookObject
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
     * Set from_app
     *
     * @param integer $fromApp
     * @return FacebookObject
     */
    public function setFromApp($fromApp)
    {
        $this->from_app = $fromApp;
    
        return $this;
    }

    /**
     * Get from_app
     *
     * @return integer 
     */
    public function getFromApp()
    {
        return $this->from_app;
    }

    /**
     * Set created_by
     *
     * @param integer $createdBy
     * @return FacebookObject
     */
    public function setCreatedBy($createdBy)
    {
        $this->created_by = $createdBy;
    
        return $this;
    }

    /**
     * Get created_by
     *
     * @return integer 
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set advert_id
     *
     * @param integer $advertId
     * @return FacebookObject
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
     * Set user_id
     *
     * @param integer $userId
     * @return FacebookObject
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
     * Set qa_position_id
     *
     * @param integer $qaPositionId
     * @return FacebookObject
     */
    public function setQaPositionId($qaPositionId)
    {
        $this->qa_position_id = $qaPositionId;
    
        return $this;
    }

    /**
     * Get qa_position_id
     *
     * @return integer 
     */
    public function getQaPositionId()
    {
        return $this->qa_position_id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return FacebookObject
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
     * @return FacebookObject
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
     * Set advert
     *
     * @param Proton\RigbagBundle\Entity\Advert $advert
     * @return FacebookObject
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
     * @return FacebookObject
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
     * Set qaPosition
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $qaPosition
     * @return FacebookObject
     */
    public function setQaPosition(\Proton\RigbagBundle\Entity\QaPosition $qaPosition = null)
    {
        $this->qaPosition = $qaPosition;
    
        return $this;
    }

    /**
     * Get qaPosition
     *
     * @return Proton\RigbagBundle\Entity\QaPosition 
     */
    public function getQaPosition()
    {
        return $this->qaPosition;
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
