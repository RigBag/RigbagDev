<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\Location
 */
class Location
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
     * @var string $code
     */
    private $code;

    /**
     * @var string $lat
     */
    private $lat;

    /**
     * @var string $lng
     */
    private $lng;

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
     * Set name
     *
     * @param string $name
     * @return Location
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
    	if( $this->name ) {
        	return $this->name;
    	}
    	return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Location
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Location
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     * @return Location
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    
        return $this;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Location
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
     * @return Location
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
     * Constructor
     */
    public function __construct()
    {
        $this->circles = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add circles
     *
     * @param Proton\RigbagBundle\Entity\Circle $circles
     * @return Location
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