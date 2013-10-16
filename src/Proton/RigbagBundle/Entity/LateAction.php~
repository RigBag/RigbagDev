<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\LateAction
 */
class LateAction
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
     * @var string $action_type
     */
    private $action_type;

    /**
     * @var string $action_params
     */
    private $action_params;

    /**
     * @var \DateTime $expired_at
     */
    private $expired_at;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $dictionary;

    
    
    public function run( $config ) {
    	
    	switch( $this->action_type ) {
    		
    		case 'post_advert_facebook':
//     			$service	= new \ProtonLabs_Facebook_Service(
//     					$config['facebook']['application_id'],
//     					$config['facebook']['application_secret'],
//     					$config['facebook']['scope']
//     			);
    				
//     			$params	= unserialize( $this->action_params );
    			
//     			$service->setAccessToken( $params['token'] );
    				
//     			$response = json_decode( $service->createAction( 'me', 'rigbag-com:publish', array( 'advert' => $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advert->getHash() ) ) ) ) );
    		break;
    		
    		case 'post_advert_twitter':
    			
    		break;
    		
    	}
    	
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
     * Set user_id
     *
     * @param integer $userId
     * @return LateAction
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
     * Set action_type
     *
     * @param string $actionType
     * @return LateAction
     */
    public function setActionType($actionType)
    {
        $this->action_type = $actionType;
    
        return $this;
    }

    /**
     * Get action_type
     *
     * @return string 
     */
    public function getActionType()
    {
        return $this->action_type;
    }

    /**
     * Set action_params
     *
     * @param string $actionParams
     * @return LateAction
     */
    public function setActionParams($actionParams)
    {
        $this->action_params = $actionParams;
    
        return $this;
    }

    /**
     * Get action_params
     *
     * @return string 
     */
    public function getActionParams()
    {
        return $this->action_params;
    }

    /**
     * Set expired_at
     *
     * @param \DateTime $expiredAt
     * @return LateAction
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expired_at = $expiredAt;
    
        return $this;
    }

    /**
     * Get expired_at
     *
     * @return \DateTime 
     */
    public function getExpiredAt()
    {
        return $this->expired_at;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return LateAction
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
     * Set dictionary
     *
     * @param Proton\RigbagBundle\Entity\User $dictionary
     * @return LateAction
     */
    public function setDictionary(\Proton\RigbagBundle\Entity\User $dictionary = null)
    {
        $this->dictionary = $dictionary;
    
        return $this;
    }

    /**
     * Get dictionary
     *
     * @return Proton\RigbagBundle\Entity\User 
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
    	$this->created_at	= new \DateTime();
    }
    /**
     * @var Proton\RigbagBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param Proton\RigbagBundle\Entity\User $user
     * @return LateAction
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