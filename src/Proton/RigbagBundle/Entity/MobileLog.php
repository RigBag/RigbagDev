<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileLog
 */
class MobileLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $data_dump;

    /**
     * @var \DateTime
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
     * Set user_id
     *
     * @param integer $userId
     * @return MobileLog
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
     * Set model
     *
     * @param string $model
     * @return MobileLog
     */
    public function setModel($model)
    {
        $this->model = $model;
    
        return $this;
    }

    /**
     * Get model
     *
     * @return string 
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return MobileLog
     */
    public function setVersion($version)
    {
        $this->version = $version;
    
        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return MobileLog
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    
        return $this;
    }

    /**
     * Get platform
     *
     * @return string 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return MobileLog
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set data_dump
     *
     * @param string $dataDump
     * @return MobileLog
     */
    public function setDataDump($dataDump)
    {
        $this->data_dump = $dataDump;
    
        return $this;
    }

    /**
     * Get data_dump
     *
     * @return string 
     */
    public function getDataDump()
    {
        return $this->data_dump;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return MobileLog
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
