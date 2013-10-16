<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\UserOption
 */
class UserOption
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
     * @var string $option_key
     */
    private $option_key;

    /**
     * @var string $option_value
     */
    private $option_value;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $user;


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
     * @return UserOption
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
     * Set option_key
     *
     * @param string $optionKey
     * @return UserOption
     */
    public function setOptionKey($optionKey)
    {
        $this->option_key = $optionKey;
    
        return $this;
    }

    /**
     * Get option_key
     *
     * @return string 
     */
    public function getOptionKey()
    {
        return $this->option_key;
    }

    /**
     * Set option_value
     *
     * @param string $optionValue
     * @return UserOption
     */
    public function setOptionValue($optionValue)
    {
        $this->option_value = $optionValue;
    
        return $this;
    }

    /**
     * Get option_value
     *
     * @return string 
     */
    public function getOptionValue()
    {
        return $this->option_value;
    }

    /**
     * Set user
     *
     * @param Proton\RigbagBundle\Entity\User $user
     * @return UserOption
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
}