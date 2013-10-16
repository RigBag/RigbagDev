<?php

namespace Proton\RigbagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proton\RigbagBundle\Entity\News
 */
class News
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var \DateTime $add_date
     */
    private $add_date;

    /**
     * @var string $tw_id
     */
    private $tw_id;

    /**
     * @var string $content
     */
    private $content;

    /**
     * @var integer $tw_user_id
     */
    private $tw_user_id;

    /**
     * @var string $tw_user_name
     */
    private $tw_user_name;

    /**
     * @var string $tw_user_url
     */
    private $tw_user_url;

    /**
     * @var string $tw_user_picture
     */
    private $tw_user_picture;


    
    public function getAddedAgo() {
    
    
    	$date	= strtotime( $this->add_date->format('Y-m-d H:i:s') );
    	$now	= time();
    	$time	= $now - $date;
    
    	$days		= floor( $time / ( 60 * 60 * 24 ) );
    	$hours		= 0;
    	$minutes	= 0;
    
    	if( $days < 1 ) {
    
    		$rest	= $time - ( $days * 60 * 60 * 24 );
    
    		$hours	= floor( $rest / ( 60 * 60 ) );
    
    		if( $hours < 1 ) {
    			$rest	= $time - ( $days * 60 * 60 );
    
    			$minutes	= floor( $rest / 60 );
    		}
    	}
    
    	return	array(
    			'days'		=> $days,
    			'minutes'	=> $minutes,
    			'hours'		=> $hours
    	);
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
     * Set add_date
     *
     * @param \DateTime $addDate
     * @return News
     */
    public function setAddDate($addDate)
    {
        $this->add_date = $addDate;
    
        return $this;
    }

    /**
     * Get add_date
     *
     * @return \DateTime 
     */
    public function getAddDate()
    {
        return $this->add_date;
    }

    /**
     * Set tw_id
     *
     * @param string $twId
     * @return News
     */
    public function setTwId($twId)
    {
        $this->tw_id = $twId;
    
        return $this;
    }

    /**
     * Get tw_id
     *
     * @return string 
     */
    public function getTwId()
    {
        return $this->tw_id;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return News
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set tw_user_id
     *
     * @param integer $twUserId
     * @return News
     */
    public function setTwUserId($twUserId)
    {
        $this->tw_user_id = $twUserId;
    
        return $this;
    }

    /**
     * Get tw_user_id
     *
     * @return integer 
     */
    public function getTwUserId()
    {
        return $this->tw_user_id;
    }

    /**
     * Set tw_user_name
     *
     * @param string $twUserName
     * @return News
     */
    public function setTwUserName($twUserName)
    {
        $this->tw_user_name = $twUserName;
    
        return $this;
    }

    /**
     * Get tw_user_name
     *
     * @return string 
     */
    public function getTwUserName()
    {
        return $this->tw_user_name;
    }

    /**
     * Set tw_user_url
     *
     * @param string $twUserUrl
     * @return News
     */
    public function setTwUserUrl($twUserUrl)
    {
        $this->tw_user_url = $twUserUrl;
    
        return $this;
    }

    /**
     * Get tw_user_url
     *
     * @return string 
     */
    public function getTwUserUrl()
    {
        return $this->tw_user_url;
    }

    /**
     * Set tw_user_picture
     *
     * @param string $twUserPicture
     * @return News
     */
    public function setTwUserPicture($twUserPicture)
    {
        $this->tw_user_picture = $twUserPicture;
    
        return $this;
    }

    /**
     * Get tw_user_picture
     *
     * @return string 
     */
    public function getTwUserPicture()
    {
        return $this->tw_user_picture;
    }
    /**
     * @var string $tw_user_screen_name
     */
    private $tw_user_screen_name;


    /**
     * Set tw_user_screen_name
     *
     * @param string $twUserScreenName
     * @return News
     */
    public function setTwUserScreenName($twUserScreenName)
    {
        $this->tw_user_screen_name = $twUserScreenName;
    
        return $this;
    }

    /**
     * Get tw_user_screen_name
     *
     * @return string 
     */
    public function getTwUserScreenName()
    {
        return $this->tw_user_screen_name;
    }
}