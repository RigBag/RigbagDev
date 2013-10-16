<?php

namespace Proton\RigbagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends \ProtonLabs_Controller
{
    public function indexAction( Request $request )
    {
		$this->setupLocale($request);
		$locale = $request->getLocale();
    	$isLoged	= $this->isLoged();
    	$setUrl		= '';
    	$em 		= $this->getDoctrine()->getManager();
    	$user		= null;

    	// PROMO_CODE
    	$promoCode		= $request->get( 'pc', null );
    	if( !is_null( $promoCode ) ) {

    		$code			= $em->getRepository( 'ProtonRigbagBundle:PromoCode' )->findOneBy( array( 'code' => $promoCode ) );

    		if( $code ) {
    			$this->get( 'session' )->set( 'promoCode', $code->getId() );
    		}
    	}


    	if( $isLoged ) {
    		$this->get('session')->set( 'userId', null );
    		$userId	= $this->getUserId();
    		$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
    		$userId	= null;
    	} else {
    		$userId	= $this->get('session')->get( 'userId', null );

    		if( $userId ) {

	    		$em 	= $this->getDoctrine()->getManager();
	    		$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

	    		$profileFilled	= $user->getOptionValue( 'profile_filled' );
	    		$dataFilled		= true;
	    		$redirectTo		= 1;

	    		if( $profileFilled ) {
	    			$sportsFilled	= $user->getOptionValue( 'sports_filled' );
	    			if( $sportsFilled ) {
	    				$subscriptionFilled	= $user->getOptionValue( 'subscription_filled' );

	    				if( !$subscriptionFilled ) {
	    					$redirectTo		= 4;
	    					$dataFilled		= false;
	    				}
	    			} else {
	    				$redirectTo		= 3;
	    				$dataFilled		= false;
	    			}
	    		} else {
	    			$redirectTo	= 2;
	    			$dataFilled	= false;
	    		}

	    		if( $dataFilled ) {
	    			$userData		= array(
	    					'id'			=> $user->getId(),
	    					'description'	=> $user->getName()
	    			);

	    			$this->loginUser( $userData );

	    			$this->get( 'session' )->set( 'userId', null );
	    			$userId		= null;
	    			$isLoged	= true;
	    		} else {

	    			switch( $redirectTo ) {
	    				case 2:
							$setUrl	= '/signup/profile/';
						break;
						case 3:
							$setUrl	= '/signup/mysports/';
						break;
						case 4:
							$setUrl	= '/signup/subscription/';
						break;
	    			}

	    		}
    		}
    	}

    	$metaTagsInfo	= $this->get( 'session' )->get( 'metaTags', null );
    	$config			= $this->container->getParameter( 'social' );
    	$this->get( 'session' )->set( 'metaTags', null );

    	$ogpObject	= new \ProtonLabs_Facebook_OGP_Object();

    	$ogpObject->setAppId( $config['facebook']['application_id'] );
    	$ogpObject->addNamespace( 'rigbag-com: http://ogp.me/ns/fb/rigbag-com#' );
    	$ogpObject->setSiteName( 'RigBag');
    	$ogpObject->setTitle( 'RigBag . Powering Action Sports' );
    	$ogpObject->setLocale( 'en_US' );
    	$rendered	= false;


    	if( !$rendered ) {

    		$ogpObject->setDescription( 'RigBag - Bringing the Action Sports Community together to share value.' );
    		$ogpObject->setUrl( $this->getHost() );
    		$ogpObject->setType( 'website' );

    		$ogpObject->addImage( array(
    			'url'		=> $this->getHost() . '/bundles/protonrigbag/img/rb-cover.png',
    			'width'		=> 300,
    			'height'	=> 300,
    			'type'		=> 'image/png'
    		) );

    	}



    	$isLoged 	= is_null( $isLoged ) ? false : $isLoged;
    	$userId		= is_null( $userId ) ? 0 : $userId;

    	return $this->render('ProtonRigbagBundle:Main:index.html.twig', array( 'isTablet' => $this->isTablet(), 'ogpObject' => $ogpObject, 'bodyClass' => 'adverts', 'user' => $user, 'isLoged' => $isLoged, 'userId' => $userId, 'setUrl' => $setUrl, 'locale' => $locale ) );
    }
}