<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\Circle
 */
class Circle
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $interest_id
     */
    private $interest_id;

    /**
     * @var integer $location_id
     */
    private $location_id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     */
    private $updated_at;

    /**
     * @var Proton\RigbagBundle\Entity\Location
     */
    private $location;

    /**
     * @var Proton\RigbagBundle\Entity\Interest
     */
    private $interest;


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
     * Set interest_id
     *
     * @param integer $interestId
     * @return Circle
     */
    public function setInterestId($interestId)
    {
        $this->interest_id = $interestId;
    
        return $this;
    }

    /**
     * Get interest_id
     *
     * @return integer 
     */
    public function getInterestId()
    {
        return $this->interest_id;
    }

    /**
     * Set location_id
     *
     * @param integer $locationId
     * @return Circle
     */
    public function setLocationId($locationId)
    {
        $this->location_id = $locationId;
    
        return $this;
    }

    /**
     * Get location_id
     *
     * @return integer 
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Circle
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
     * Set description
     *
     * @param string $description
     * @return Circle
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Circle
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
     * @return Circle
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
     * Set location
     *
     * @param Proton\RigbagBundle\Entity\Location $location
     * @return Circle
     */
    public function setLocation(\Proton\RigbagBundle\Entity\Location $location = null)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return Proton\RigbagBundle\Entity\Location 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set interest
     *
     * @param Proton\RigbagBundle\Entity\Interest $interest
     * @return Circle
     */
    public function setInterest(\Proton\RigbagBundle\Entity\Interest $interest = null)
    {
        $this->interest = $interest;
    
        return $this;
    }

    /**
     * Get interest
     *
     * @return Proton\RigbagBundle\Entity\Interest 
     */
    public function getInterest()
    {
        return $this->interest;
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
     * Constructor
     */
    public function __construct()
    {
        $this->adverts = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add adverts
     *
     * @param Proton\RigbagBundle\Entity\Advert $adverts
     * @return Circle
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
    private $users;


    /**
     * Add users
     *
     * @param Proton\RigbagBundle\Entity\User $users
     * @return Circle
     */
    public function addUser(\Proton\RigbagBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param Proton\RigbagBundle\Entity\User $users
     */
    public function removeUser(\Proton\RigbagBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $questions;


    /**
     * Add questions
     *
     * @param Proton\RigbagBundle\Entity\QaPosition $questions
     * @return Circle
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
}