<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\TmpUpload
 */
class TmpUpload
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
     * @var string $path
     */
    private $path;

    /**
     * @var string $session_key
     */
    private $session_key;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;


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
     * @return TmpUpload
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
     * Set path
     *
     * @param string $path
     * @return TmpUpload
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
     * Set session_key
     *
     * @param string $sessionKey
     * @return TmpUpload
     */
    public function setSessionKey($sessionKey)
    {
        $this->session_key = $sessionKey;
    
        return $this;
    }

    /**
     * Get session_key
     *
     * @return string 
     */
    public function getSessionKey()
    {
        return $this->session_key;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return TmpUpload
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
     * @ORM\PrePersist
     */
 	public function setCreatedAtValue()
    {
    	$this->created_at	= new \DateTime();
    }
}