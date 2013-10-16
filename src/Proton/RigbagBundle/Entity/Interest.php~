<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\Interest
 */
class Interest
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
     * @var string $picture
     */
    private $picture;

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
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Interest
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
     * Set picture
     *
     * @param string $picture
     * @return Interest
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    
        return $this;
    }

    /**
     * Get picture
     *
     * @return string 
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Interest
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
     * @return Interest
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
     * Add users
     *
     * @param Proton\RigbagBundle\Entity\User $users
     * @return Interest
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
    private $circles;


    /**
     * Add circles
     *
     * @param Proton\RigbagBundle\Entity\Circle $circles
     * @return Interest
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
}