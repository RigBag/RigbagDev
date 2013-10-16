<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\AdvertImage
 */
class AdvertImage
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $advert_id
     */
    private $advert_id;

    /**
     * @var string $path
     */
    private $path;

    /**
     * @var string $is_main
     */
    private $is_main;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var Proton\RigbagBundle\Entity\Advert
     */
    private $advert;


    public function getExtension() {
    	$tmp	= pathinfo( $this->path );
    	return $tmp['extension'];
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
     * Set advert_id
     *
     * @param integer $advertId
     * @return AdvertImage
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
     * Set path
     *
     * @param string $path
     * @return AdvertImage
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set is_main
     *
     * @param string $isMain
     * @return AdvertImage
     */
    public function setIsMain($isMain)
    {
        $this->is_main = $isMain;
    
        return $this;
    }

    /**
     * Get is_main
     *
     * @return string 
     */
    public function getIsMain()
    {
        return $this->is_main;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return AdvertImage
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
     * Set advert
     *
     * @param Proton\RigbagBundle\Entity\Advert $advert
     * @return AdvertImage
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
}