<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\DictionaryValue
 */
class DictionaryValue
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $dictionary_id
     */
    private $dictionary_id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var integer $ord
     */
    private $ord;

    /**
     * @var string $state
     */
    private $state;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     */
    private $updated_at;

    /**
     * @var Proton\RigbagBundle\Entity\Dictionary
     */
    private $location;


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
     * Set dictionary_id
     *
     * @param integer $dictionaryId
     * @return DictionaryValue
     */
    public function setDictionaryId($dictionaryId)
    {
        $this->dictionary_id = $dictionaryId;
    
        return $this;
    }

    /**
     * Get dictionary_id
     *
     * @return integer 
     */
    public function getDictionaryId()
    {
        return $this->dictionary_id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return DictionaryValue
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
     * @return DictionaryValue
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
     * Set ord
     *
     * @param integer $ord
     * @return DictionaryValue
     */
    public function setOrd($ord)
    {
        $this->ord = $ord;
    
        return $this;
    }

    /**
     * Get ord
     *
     * @return integer 
     */
    public function getOrd()
    {
        return $this->ord;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return DictionaryValue
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return DictionaryValue
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
     * @return DictionaryValue
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
     * @param Proton\RigbagBundle\Entity\Dictionary $location
     * @return DictionaryValue
     */
    public function setLocation(\Proton\RigbagBundle\Entity\Dictionary $location = null)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return Proton\RigbagBundle\Entity\Dictionary 
     */
    public function getLocation()
    {
        return $this->location;
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
    private $conditionOfAdverts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->conditionOfAdverts = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add conditionOfAdverts
     *
     * @param Proton\RigbagBundle\Entity\Advert $conditionOfAdverts
     * @return DictionaryValue
     */
    public function addConditionOfAdvert(\Proton\RigbagBundle\Entity\Advert $conditionOfAdverts)
    {
        $this->conditionOfAdverts[] = $conditionOfAdverts;
    
        return $this;
    }

    /**
     * Remove conditionOfAdverts
     *
     * @param Proton\RigbagBundle\Entity\Advert $conditionOfAdverts
     */
    public function removeConditionOfAdvert(\Proton\RigbagBundle\Entity\Advert $conditionOfAdverts)
    {
        $this->conditionOfAdverts->removeElement($conditionOfAdverts);
    }

    /**
     * Get conditionOfAdverts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getConditionOfAdverts()
    {
        return $this->conditionOfAdverts;
    }
    /**
     * @var Proton\RigbagBundle\Entity\Dictionary
     */
    private $dictionary;


    /**
     * Set dictionary
     *
     * @param Proton\RigbagBundle\Entity\Dictionary $dictionary
     * @return DictionaryValue
     */
    public function setDictionary(\Proton\RigbagBundle\Entity\Dictionary $dictionary = null)
    {
        $this->dictionary = $dictionary;
    
        return $this;
    }

    /**
     * Get dictionary
     *
     * @return Proton\RigbagBundle\Entity\Dictionary 
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }
}