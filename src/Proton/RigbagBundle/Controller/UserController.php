<?php

namespace Proton\RigbagBundle\Controller;

use Proton\RigbagBundle\Repository\UserOptionRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\Location;
use Proton\RigbagBundle\Entity\Circle;
use Proton\RigbagBundle\Entity\UserOption;

class UserController extends \ProtonLabs_Controller
{
	public function shortUrlAction( $hash ) {
		$this->setupLocale($request);
		$locale = $request->getLocale();
		$em 		= $this->getDoctrine()->getManager();
		$isLoged	= $this->isLoged();
    	$user		= null;

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

    		}
    	}


    	$config			= $this->container->getParameter( 'social' );

    	$ogpObject	= new \ProtonLabs_Facebook_OGP_Object();

    	$ogpObject->setAppId( $config['facebook']['application_id'] );
    	$ogpObject->addNamespace( 'rigbag-com: http://ogp.me/ns/fb/rigbag-com#' );
    	$ogpObject->setSiteName( 'RigBag');
    	$ogpObject->setTitle( 'RigBag . Powering Action Sports' );
    	$ogpObject->setLocale( 'en_US' );
    	$rendered	= false;

   		$userView		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( \Proton\RigbagBundle\Entity\User::decodeHash( $hash ) );

   		if( $userView ) {
   			$ogpObject->setDescription( $userView->getName() . ( $userView->getBio() ? ' - ' . $userView->getBio() : '' ) );
    		$ogpObject->setUrl( $this->getHost() . $this->generateUrl( 'user_short_url', array( 'hash' => \Proton\RigbagBundle\Entity\User::encodeHash( $userView->getId() ) ) ) );
    		$ogpObject->setType( 'rigbag-com:account' );
    		$ogpObject->setTitle( $userView->getName() );

    		$ogpObject->addImage( array(
    			'url'		=> $this->getHost() . '/bundles/protonrigbag/img/rb-cover.png',
    			'width'		=> 300,
    			'height'	=> 300,
    			'type'		=> 'image/png'
    		) );
    	}
    	else {
    		return $this->redirect( $this->generateUrl( 'start' ) );
    	}

    	$isLoged 	= is_null( $isLoged ) ? false : $isLoged;
    	$userId		= is_null( $userId ) ? 0 : $userId;
    	$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->findForUser( $userView->getId() );

    	return $this->render('ProtonRigbagBundle:Main:index.html.twig', array( 'adverts' => $adverts, 'userView' => $userView, 'lockInit' => true, 'ogpObject' => $ogpObject, 'bodyClass' => 'my-profile', 'user' => $user, 'isLoged' => $isLoged, 'userId' => $userId, 'locale' => $locale ) );

	}

	public function panelRefreshAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$result		= array( 'messages' => array( 'newCount' => 0 ) );


			if( $this->isLoged() ) {
				$messages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findMessagesToUser( $this->getUserId(), 'unreaded' );
				$c			= 0;
				foreach( $messages as $m ) { $c++; }
				$result['messages']['newCount']	= $c;
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse($result, 200);
		}

		return $this->blackholeResponse();
	}
	
	public function panelMessageReadAction( $messageId, Request $request ) {
		
		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {
		
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}
		
			$em 		= $this->getDoctrine()->getManager();
			$message 	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $messageId );
			
			if( $message && $message->getToUserId() == $this->getUserId() )
			{
				$message->setReaded( 1 );
				$em->flush();
			}
		}
		
		return new JsonResponse();
	}

	public function panelRefreshMessagesAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();

			$result		= array( 'content' => '' );

			$engine 	= $this->container->get('templating');

			if( $this->isLoged() ) {
				$messages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findMessagesToUser( $this->getUserId(), 'unreaded' );

				$result['content']	= $engine->render( 'ProtonRigbagBundle:User:panel-messages.html.twig', array( 'messages' => $messages ) );
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function searchAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$circleId	= $request->get( 'circle', 0 );
			$query		= $request->get( 'query', '' );
			$result		= array();


				$em 		= $this->getDoctrine()->getManager();
				$engine 	= $this->container->get('templating');

				if( $circleId ) {
					$circles	= array( $circleId );
				} else {
					$circles	= array();
				}

				$users		= $em->getRepository( 'ProtonRigbagBundle:User' )->search( $query, array( 'circles' => $circles ) );

				$result		= array(
						'toUpdate'	=> array( 'circleContent' ),
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-content.html.twig', array( 'members' => $users ) )
				);


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function circlesAction( $userId, $mode, Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

			if( $userId == $this->getUserId() ) {
				$myProfile	= true;
			} else {
				$myProfile	= false;
			}

			if( $mode == 'simple' ) {
				$result		= array(
										'toUpdate'	=> array( 'content' ),
										'bodyClass'	=> '',
										'content'	=> $engine->render( 'ProtonRigbagBundle:User:profile-content-circles.html.twig', array( 'circles' => $user->getCircles(), 'user' => $user, 'myProfile' => $myProfile ) )
								);
			} else {
				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:User:profile-header-top.html.twig', array( 'myProfile' => $myProfile, 'user' => $user ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:profile-header-bottom.html.twig', array( 'type' => 'circles', 'myProfile' => $myProfile, 'user' => $user ) )
						),
						'bodyClass'	=> 'my-profile',
						'content'	=> $engine->render( 'ProtonRigbagBundle:User:profile-content-circles.html.twig', array( 'circles' => $user->getCircles(), 'user' => $user, 'myProfile' => $myProfile ) )
				);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function subscriptionSelectAction( $type, Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');

			$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			switch( $type ) {
				case 'free':
					$user->setAccountType( 'free' );
					$user->setExpiredAt( null );

					$subscriptionFilled	= $user->getOptionValue( 'subscription_filled' );
					if( !$subscriptionFilled ) {
						$uo	= new UserOption();
						$uo->setOptionKey( 'subscription_filled' )
								->setOptionValue( date('Y-m-d H:i:s' ) )
								->setUser( $user );

						$user->addOption( $uo );

						$em->persist( $uo );
					}
				break;
				case 'annual':

					return $this->redirect( $this->generateUrl( 'sandbox_payment_annual', array() ) );


				break;
			}

			$em->flush();

			$result		= array(
					'toUpdate'	=> array(),//'content' ),
					'content'	=> ''
			);

			return new JsonResponse( $result, 200 );
		}


		return $this->blackholeResponse();
	}

	public function doneAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$result		= array();

			if( !$this->isLoged() )
			{
				$signup		= true;
				$em 		= $this->getDoctrine()->getManager();
				$engine 	= $this->container->get('templating');
				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );


				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:User:settings-header-top.html.twig', array( 'signup' => $signup, 'step' => 4 ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:settings-header-bottom.html.twig', array( 'signup' => $signup, 'type' => 'transactions' ) )
						),
						'bodyClass'	=> 'signup-subscription',
						'content'	=> $engine->render( 'ProtonRigbagBundle:User:content-signup-done.html.twig', array( 'signup' => $signup, 'user' => $user ) )
				);
			}


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function subscriptionAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');

			if( $this->isLoged() ) {
				$signup		= false;
			} else {
				$signup		= true;
			}

			$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			$em->flush();

			$toUpdate		= array( 'headerTop', 'headerBottom', 'content', 'bodyClass' );
			$flashMessage	= $this->get('session')->get('flashMessage', null);
			$this->get('session')->set('flashMessage', null );

			if( !is_null( $flashMessage ) ) {
				$toUpdate[]	= 'flashMessage';
			}



			$result		= array(
					'toUpdate'	=> $toUpdate,
					'header'	=> array(
							'top'		=> $engine->render( 'ProtonRigbagBundle:User:settings-header-top.html.twig', array( 'signup' => $signup, 'step' => 3 ) ),
							'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:settings-header-bottom.html.twig', array( 'signup' => $signup, 'type' => 'subscription' ) )
					),
					'flashMessage'	=> $flashMessage,
					'bodyClass'		=> 'signup-subscription',
					'content'		=> $engine->render( 'ProtonRigbagBundle:User:content-subscription.html.twig', array( 'signup' => $signup, 'user' => $user ) )
			);


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function transactionsAction( Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');


			$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$transactions	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findForUser( $this->getUserId() );


			$result		= array(
							'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
							'header'	=> array(
									'top'		=> $engine->render( 'ProtonRigbagBundle:User:settings-header-top.html.twig', array('signup' => false ) ),
									'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:settings-header-bottom.html.twig', array('signup' => false, 'type' => 'transactions' ) )
							),
							'bodyClass'	=> 'transactions',
							'content'	=> $engine->render( 'ProtonRigbagBundle:User:content-transactions.html.twig', array( 'signup' => false, 'user' => $user, 'transactions' => $transactions ) )
					);

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function qaAction( $userId, $mode, Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');


			if( $userId	== $this->getUserId() || !$userId ) {
				$myProfile	= true;
				$userId		= $this->getUserId();
			} else {
				$myProfile	= false;
			}

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$positions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findUserThreads( $userId );


			if( $mode == 'simple' ) {
				$result		= array(
										'toUpdate'	=> array( 'content' ),
										'content'	=> $engine->render( 'ProtonRigbagBundle:User:profile-content-qa.html.twig', array( 'user' => $user, 'myProfile' => $myProfile, 'questions' => $positions ) )
								);
			} else {
				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:User:profile-header-top.html.twig', array( 'myProfile' => $myProfile, 'user' => $user ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:profile-header-bottom.html.twig', array( 'type' => 'qa', 'myProfile' => $myProfile, 'user' => $user ) )
						),
						'bodyClass'	=> 'my-profile',
						'content'	=> $engine->render( 'ProtonRigbagBundle:User:profile-content-qa.html.twig', array( 'user' => $user, 'myProfile' => $myProfile, 'questions' => $positions ) )
				);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function profileAction( $userId, Request $request ) {

		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {

			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();

			$engine 	= $this->container->get('templating');

			if( $userId	== $this->getUserId() || !$userId ) {
				$myProfile	= true;
				$userId		= $this->getUserId();
			} else {
				$myProfile	= false;
			}

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->findForUser( $userId );


			$result		= array(
									'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
									'header'	=> array(
											'top'		=> $engine->render( 'ProtonRigbagBundle:User:profile-header-top.html.twig', array( 'myProfile' => $myProfile, 'user' => $user ) ),
											'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:profile-header-bottom.html.twig', array( 'type' => 'adverts',  'myProfile' => $myProfile, 'user' => $user ) )
									),
									'bodyClass'	=> 'my-profile',
									'content'	=> $engine->render( 'ProtonRigbagBundle:User:profile-content-adverts.html.twig', array( 'adverts' => $adverts, 'user' => $user, 'myProfile' => $myProfile ) )
							);


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}


		return $this->blackholeResponse();
	}

	public function disconnectAction( $type, Request $request )
	{
		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {
			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$result		= array();

			$em 	= $this->getDoctrine()->getManager();

			if( $this->isLoged() ) {
				$userId	= $this->getUserId();
			} else {
				$userId	= $this->get( 'session' )->get('userId');
			}

			$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

			switch( $type ) {
				case 'facebook':
					$user->setFacebookId( null );
				break;
				case 'twitter':
					$user->setTwitterId( null );
				break;
				case 'google':
					$this->get('session')->set( 'gpToken', null );
					$user->setGoogleId( null );
				break;
			}

			$em->flush( $user );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function connectAction( $type, Request $request )
	{
		$this->setupLocale($request);
		if ($request->isXmlHttpRequest()) {
			if( !$this->auth( false ) ) {
				return $this->unAuthResponse();
			}

			$result		= array( 'canConnect' => true );

			$em 	= $this->getDoctrine()->getManager();

			$userId	= $this->getUserId();

			$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

			$this->get( 'session' )->set( 'bckData', $request->get( 'bckData' ) );
			$this->get( 'session' )->set( 'backUrlPath', '#/settings/profile/reload/' );

			switch( $type ) {
				case 'facebook':
					if( is_null( $user->getFacebookId() ) ) {

					} else {
						$result['canConnect']		= false;
					}
				break;
				case 'twitter':
					if( is_null( $user->getTwitterId() ) ) {

					} else {
						$result['canConnect']		= false;
					}
				break;
				case 'google':
					if( is_null( $user->getGoogleId() ) ) {

					} else {
						$result['canConnect']		= false;
					}
				break;
			}

			return new JsonResponse( $result, 200 );
		}


		return $this->blackholeResponse();
	}

    public function settingsAction( Request $request )
    {
    	$this->setupLocale($request);
    	if ($request->isXmlHttpRequest()) {

    		if( !$this->auth( false ) ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();


	    	$engine 	= $this->container->get('templating');

	    	if( $this->isLoged() ) {
	    		$signup		= false;
	    	} else {
	    		$signup		= true;
	    	}

	    	$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

	    	$reload		= $request->get( 'r', null );
	    	if( $reload ) {
	    		$bckData	= $this->get( 'session' )->get( 'bckData', null );
	    		if( is_array( $bckData ) ) {
	    			$user->setName( $bckData['name'] )
	    				->setEmail( $bckData['email'] )
	    				->setPhone( $bckData['phone'] )
	    				->setLocation( $bckData['location'] )
	    				->setLocationCountryCode( $bckData['location_cc'] )
	    				->setLocationFormated( $bckData['location_formated'] )
	    				->setLocationLat( $bckData['location_lat'] )
	    				->setLocationLng( $bckData['location_lng'] )
	    				->setPostCode( $bckData['postCode'] )
	    				->setBio( $bckData['bio'] );
	    		}
	    	}

	    	$paths		= $this->container->getParameter( 'paths' );


	    	$result		= array(
	    						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
	    						'header'	=> array(
	    												'top'		=> $engine->render( 'ProtonRigbagBundle:User:settings-header-top.html.twig', array( 'signup' => $signup, 'step' => 1 ) ),
	    												'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:settings-header-bottom.html.twig', array( 'type' => 'settings', 'signup' => $signup ) )
	    											),
	    						'bodyClass'	=> '',
	    						'content'	=> $engine->render( 'ProtonRigbagBundle:User:settings-content-my-profile.html.twig', array( 'user' => $user, 'signup' => $signup, 'path' => $paths['storage']['avatar'] ) )
	    					);


	    	$result['actionStamp']		= $request->get( 'actionStamp', '' );

	    	return new JsonResponse( $result, 200 );
    	}

		return $this->blackholeResponse();
    }

    public function myProfileUpdateAction( Request $request ) {

		$this->setupLocale($request);
    	if ($request->isXmlHttpRequest()) {

    		if( !$this->auth( false ) ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();

	    	$response	= new Response();
	    	$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );
	    	$result		= array( 'success' => true );

	    	$userByEmail	= $em->getRepository( 'ProtonRigbagBundle:User' )->findOneBy( array( 'email' => $request->get( 'email' ) ) );

	    	if( $userByEmail && $userByEmail->getId() != $this->getUserId() ) {
	    		$result['success']		= false;
	    		$result['errorFields']	= array( 'f-email' );
	    	} else {

		    	$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

		    	$profileFilled	= $user->getOptionValue( 'profile_filled' );
		    	if( !$profileFilled ) {
		    		$uo	= new UserOption();
		    		$uo->setOptionKey( 'profile_filled' )
		    			->setOptionValue( date('Y-m-d H:i:s' ) )
		    			->setUser( $user );

		    		$user->addOption( $uo );

		    		$em->persist( $uo );
		    	}

		    	$user->setName( $request->get( 'name' ) )
		    			->setBio( $request->get( 'bio' ) )
		    			->setLocation( $request->get( 'location' ) )
		    			->setLocationCountryCode( $request->get( 'location_cc' ) )
		    			->setLocationFormated( $request->get( 'location_formated' ) )
		    			->setLocationLat( $request->get( 'location_lat' ) )
		    			->setLocationLng( $request->get( 'location_lng' ) )
		    			->setPostCode( $request->get( 'postCode' ) )
		    			->setPhone( $request->get( 'phone' ) )
		    			->setPaypalId( $request->get( 'paypalId') )
		    			->setEmail( $request->get( 'email' ) );

		    	$location	= $em->getRepository( 'ProtonRigbagBundle:Location' )->findOneBy( array( 'code' => $request->get('location_cc' ) ) );

				$result['updateImage'] = $this->container->getParameter('azure.storage.url') . str_replace('%size%', '36x36', $user->getProfilePicture());

		    	if( !$location ) {

		    		$location	= new Location();

		    		$location->setName('')
		    					->setCode( $request->get( 'location_cc' ) );

		    		$em->persist( $location );
		    	}

		    	$em->flush();
		    	$this->get( 'session' )->set( 'bckData', null );
	    	}

	    	return new JsonResponse( $result, 200 );
    	}

    	return $this->blackholeResponse();
    }

    public function mySportSetFilledAction( Request $request )
    {
    	$this->setupLocale($request);
    	if ($request->isXmlHttpRequest()) {
	    	if( !$this->auth( false ) ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();
	    	$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

	    	$sportsFilled	= $user->getOptionValue( 'sports_filled' );
	    	if( !$sportsFilled ) {
	    		$uo	= new UserOption();
	    		$uo->setOptionKey( 'sports_filled' )
			    		->setOptionValue( date('Y-m-d H:i:s' ) )
			    		->setUser( $user );

	    		$user->addOption( $uo );

	    		$em->persist( $uo );
	    	}

	    	$em->flush();

	    	$result	= array();

	    	$result['actionStamp']		= $request->get( 'actionStamp', '' );

	    	return new JsonResponse( $result, 200 );
    	}

		return $this->blackholeResponse();
    }

    public function mySportUpdateAction( Request $request ) {

		$this->setupLocale($request);
    	if ($request->isXmlHttpRequest()) {
	    	if( !$this->auth( false ) ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();

	    	$response	= new Response();
	    	$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );
	    	$result		= array();

	    	$userId		= $this->getUserId();
	    	$sportId	= $request->get( 'id' );
	    	$action		= $request->get( 'action' );

	    	$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
	    	$sport		= $em->getRepository( 'ProtonRigbagBundle:Interest' )->find( $sportId );

	    	switch( $action ) {
	    		case 'add':
	    			if (!$user->hasInterest($sport)) {
	    				$user->addInterest( $sport );	
	    			}

	    			//$location	= $em->getRepository( 'ProtonRigbagBundle:Location' )->findOneBy( array( 'code' => $user->getLocationCountryCode() ) );
	    			//if( $location ) {
	    				$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle')->findOneBy( array( 'interest_id' => $sport->getId()) );

	    				if( !$circle ) {
	    					$circle	= new Circle();
	    					$circle->setName( $sport->getName() )
			    					->setInterest( $sport );

	    					$em->persist( $circle );
	    				}
	    				if(!$user->hasCircle( $circle )){
	    					$user->addCircle( $circle );	
	    				}
	    				
	    			//}
	    		break;
	    		case 'remove':
	    			if ($user->hasInterest($sport)) {
	    				$user->removeInterest( $sport );	
	    			}
	    		break;
	    	}



	    	$em->flush();

	    	return new JsonResponse( $result, 200 );
    	}

    	return $this->blackholeResponse();

    }

    public function mySportsAction( Request $request ) {

		$this->setupLocale($request);
    	if ($request->isXmlHttpRequest()) {

	    	if( !$this->auth( false ) ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();

	    	$engine 	= $this->container->get('templating');

	    	if( $this->isLoged() ) {
	    		$signup		= false;
	    	} else {
	    		$signup		= true;
	    	}

	    	$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
	    	$interests		= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findAll();
	    	$tmp			= $user->getInterests();
	    	$selected		= array();

	    	foreach( $tmp as $t ) {
	    		$selected[$t->getId()]		= true;
	    	}
	    	foreach( $interests as $i ) {
	    		if( !key_exists( $i->getId(), $selected ) ) {
					$selected[$i->getId()]	= false;
	    		}
	    	}

	    	$result		= array(
				    			'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
				    			'header'	=> array(
				    					'top'		=> $engine->render( 'ProtonRigbagBundle:User:settings-header-top.html.twig', array('signup' => $signup, 'step' => 2) ),
				    					'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:settings-header-bottom.html.twig', array( 'signup' => $signup, 'type' => 'mysports' ) )
				    			),
				    			'bodyClass'	=> 'my-sports',
				    			'content'	=> $engine->render( 'ProtonRigbagBundle:User:settings-content-my-sports.html.twig', array(
				    																											'interests'		=> $interests,
				    																											'selected'		=> $selected,
				    																											'signup' 		=> $signup
				    																										) )
	    	);


	    	$result['actionStamp']		= $request->get( 'actionStamp', '' );

	    	return new JsonResponse( $result, 200 );
    	}

		return $this->blackholeResponse();
	}
}
