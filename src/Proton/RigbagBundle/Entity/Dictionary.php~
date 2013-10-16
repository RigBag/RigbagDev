<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\Dictionary
 */
class Dictionary
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
     * @var string $is_opened
     */
    private $is_opened;

    /**
     * @var string $is_updated
     */
    private $is_updated;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Dictionary
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
     * Set code
     *
     * @param string $code
     * @return Dictionary
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
     * Set is_opened
     *
     * @param string $isOpened
     * @return Dictionary
     */
    public function setIsOpened($isOpened)
    {
        $this->is_opened = $isOpened;
    
        return $this;
    }

    /**
     * Get is_opened
     *
     * @return string 
     */
    public function getIsOpened()
    {
        return $this->is_opened;
    }

    /**
     * Set is_updated
     *
     * @param string $isUpdated
     * @return Dictionary
     */
    public function setIsUpdated($isUpdated)
    {
        $this->is_updated = $isUpdated;
    
        return $this;
    }

    /**
     * Get is_updated
     *
     * @return string 
     */
    public function getIsUpdated()
    {
        return $this->is_updated;
    }

    /**
     * Add values
     *
     * @param Proton\RigbagBundle\Entity\DictionaryValue $values
     * @return Dictionary
     */
    public function addValue(\Proton\RigbagBundle\Entity\DictionaryValue $values)
    {
        $this->values[] = $values;
    
        return $this;
    }

    /**
     * Remove values
     *
     * @param Proton\RigbagBundle\Entity\DictionaryValue $values
     */
    public function removeValue(\Proton\RigbagBundle\Entity\DictionaryValue $values)
    {
        $this->values->removeElement($values);
    }

    /**
     * Get values
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getValues()
    {
        return $this->values;
    }
}