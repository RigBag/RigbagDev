<?php

namespace Proton\RigbagBundle\Controller;

use WindowsAzure\Common\ServicesBuilder;

use WindowsAzure\Blob\Models\CreateBlobOptions;

use Proton\RigbagBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends \ProtonLabs_Controller
{

	public function indexAction() {

		if( $this->isLoged() ) {
			$em 		= $this->getDoctrine()->getManager();
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
		} else {
			$user		= null;
		}

		return $this->render('ProtonRigbagBundle:Default:index.html.twig', array( 'user' => $user, 'bodyClass' => 'adverts', 'isLoged' => $this->isLoged() ) );
	}
	
	public function loginMobileAction( Request $request ) {
		
		exit();
	}

	public function loginAction( $type, Request $request ) {
		$this->setupLocale($request);
		$em 		= $this->getDoctrine()->getManager();
		$config		= $this->container->getParameter( 'social' );

		// PROMO CODE
		$promoCode	= $this->get( 'session' )->get( 'promoCode', null );

		$profilePicData	= null;

		switch( $type ) {

			// TWITTER
			case 'twitter':

				$tmhOAuth = new \TmhOAuth_Main(array(
													'consumer_key' 		=> $config['twitter']['consumer_key'],
													'consumer_secret' 	=> $config['twitter']['consumer_secret'],
    												'curl_ssl_verifypeer'   => false
												));

				$here 		= \TmhOAuth_Utilities::php_self();
				$twDone		= false;

				if ( $request->get( 'wipe', null ) ) {
					return $this->redirect( $here );
					exit();

				} elseif ( $this->get('session')->get( 'twToken', null ) ) {
					$twToken	= $this->get('session')->get( 'twToken' );

					$tmhOAuth->config['user_token'] 	= $twToken['oauth_token'];
					$tmhOAuth->config['user_secret'] 	= $twToken['oauth_token_secret'];

					$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials'));

					if ($code == 200) {
						$resp = json_decode($tmhOAuth->response['response']);
						$email			= '';
						$twId			= $resp->id;
						$displayName	= $resp->name;
						$bio			= $resp->description;

						if( $resp->profile_image_url ) {
							$profilePicData	= 	$resp->profile_image_url;
						}

						$twDone	= true;
					} else {
						//$tmhOAuth->outputError($tmhOAuth);
					}
					//
				} elseif ( $request->get( 'oauth_verifier', null ) ) {
					$twOAuth		= $this->get('session')->get( 'twOAuth' );

					$tmhOAuth->config['user_token'] 	= $twOAuth['oauth_token'];
					$tmhOAuth->config['user_secret'] 	= $twOAuth['oauth_token_secret'];

					$code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
														'oauth_verifier' => $request->get( 'oauth_verifier' )
												));

					if ($code == 200) {
						$this->get( 'session' )->set( 'twToken', $tmhOAuth->extract_params($tmhOAuth->response['response']) );
						$this->get( 'session' )->set( 'twOAuth', null );
						return $this->redirect( $here );
						exit();
					} else {
						//$tmhOAuth->outputError($tmhOAuth);
					}
					//
				} elseif ( $request->get( 'authenticate', null ) || $request->get( 'authorize', null ) ) {
					//if( $request->get( 'oob', null ) ) {
						$callback 	= 'oob';
					//} else {
						$callback	= $here;
					//}

					$params = array(
									'oauth_callback' => $callback
								);

					if ( $request->get( 'force_write', null ) ) {
						$params['x_auth_access_type'] = 'write';
					}
					elseif ( $request->get( 'force_read', null ) ) {
						$params['x_auth_access_type'] = 'read';
					}

					$code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);

					if ($code == 200) {

						$this->get('session')->set( 'twOAuth', $tmhOAuth->extract_params($tmhOAuth->response['response']) );

						$twOAuth	= $this->get( 'session' )->get( 'twOAuth' );

						$method = $request->get( 'authenticate', null ) ? 'authenticate' : 'authorize';
						$force = $request->get( 'force', null ) ? '&force_login=1' : '';
						$authurl = $tmhOAuth->url("oauth/{$method}", '') . "?oauth_token={$twOAuth['oauth_token']}{$force}";
						echo "<script>window.location = '". $authurl . "';</script>";
						exit();

					} else {
						//$tmhOAuth->outputError($tmhOAuth);
					}
				}

				if( !$twDone ) {
					echo "<script>window.location = '" . $this->getHost() . $this->generateUrl( 'login_twitter', array( 'authenticate' => 1 ) ) . "';</script>";
					exit();
				}


				if( $this->getUserId() ) {
					$user			= $em->getRepository( 'ProtonRigbagBundle:User')->find( $this->getUserId() );
				} else {
					$user			= $em->getRepository( 'ProtonRigbagBundle:User')->findOneBy( array( 'twitter_id' => $twId ) );
				}

				if( !$user ) {

					//if( !is_null( $promoCode ) ) {
						// CREATE USER
						$user		= new User();
						$user->setName( $displayName )
								->setEmail( $email )
								->setLocation('')
								->setAccountType( 'free' )
								->setState('enabled')
								->setTwitterId( $twId )
                                                                ->setPaymentMode(1)
								->setBio( isset($bio) ? $bio : '' );

						$em->persist( $user );
					//}

				} else {
					if( !$user->getTwitterId() ) {
						$user->setTwitterId( $twId );
					}
				}
				$em->flush();

				if( $user &&  $user->getProfilePicture() && $profilePicData ) {
					$profilePicData	= null;
				}

			break;

			// GOOGLE
			case 'google':

				$backUrl	= $this->getHost() . $this->generateUrl( 'login_google', array() );

				$service	= new \ProtonLabs_Google_Service(
										$config['google']['client_id'],
										$config['google']['client_secret'],
										$config['google']['developer_key'],
										$config['google']['app_name']
								);


				$service->setBackUrl( $backUrl );

				$service->initOAuth();

				$code	= $request->get( 'code', null );

				if ( $code ) {

					$service->authenticate( $code );

					$this->get('session')->set( 'gpToken', $service->getAccessToken() );

					$exp	= explode( '?', $_SERVER['REQUEST_URI'] );

					$redirect = $this->getHost() . $exp[0];

					return $this->redirect( $redirect);
				}

				if ( $this->get('session')->get( 'gpToken', null ) ) {
					$service->setAccessToken( $this->get('session')->get( 'gpToken' ) );
				}

				if ($service->getAccessToken()) {
					$this->get( 'session' )->set('gpToken', $service->getAccessToken() );
				} else {
					$authUrl 	= $service->createAuthUrl();
					echo("<script> top.location.href='" . $authUrl . "'</script>");
					exit();
				}

				$user	= $service->getUserInfo();

				$gpId			= isset( $user['id'] ) ? $user['id'] : null;
				$email			= isset( $user['email'] ) ? $user['email'] : null;
				$displayName	= isset( $user['name'] ) ? $user['name'] : null;

				if( isset( $user['picture'] ) ) {
					$picture	= $user['picture'];
				} else {
					$picture	= null;
				}

				if( $this->getUserId() ) {
					$user			= $em->getRepository( 'ProtonRigbagBundle:User')->find( $this->getUserId() );
				} else {
					$user			= $em->getRepository( 'ProtonRigbagBundle:User')->findOneBy( array( 'google_id' => $gpId ) );
				}

				if( !$user ) {

					//if( !is_null( $promoCode ) ) {
						$user		= $em->getRepository( 'ProtonRigbagBundle:User')->findOneBy( array( 'email' => $email ) );

						if( !$user ) {
							// CREATE USER
							$user		= new User();
							$user->setName( $displayName )
									->setEmail( $email )
									->setLocation('')
									->setAccountType( 'free' )
									->setState('enabled')
									->setGoogleId( $gpId );

							$em->persist( $user );
						} else {
							$user->setGoogleId( $gpId );
						}
					//}
				} else {
					if( !$user->getGoogleId() ) {
						$user->setGoogleId( $gpId );
					}
				}
				$em->flush();

				if( $user &&  !$user->getProfilePicture() && $picture ) {
					$profilePicData	= $picture;
				}


			break;


			// FACEBOOK
			case 'facebook':



				$service	= new \ProtonLabs_Facebook_Service(
									$config['facebook']['application_id'],
									$config['facebook']['application_secret'],
									$config['facebook']['scope']
							);


				$backUrl   	= $this->getHost() . $this->generateUrl( 'login_facebook', array() );
				$code 		= $request->get( 'code', null );

				if( !$code ) {

					$state	= md5( uniqid(rand(), true ) );
					$this->get('session')->set( 'state', $state );

					$dialogUrl	= $service->generateAuthDialogUrl( $backUrl, $state );

					echo("<script> top.location.href='" . $dialogUrl . "'</script>");
					exit();
				}
				
				
				$sessionState	= $this->get('session')->get('state', null );
				$requestState	= $request->get('state', null );

				
				if( $service->checkAuthState( $sessionState, $requestState ) ) {

					$accessToken	= $service->readAccessToken( $backUrl, $code );
					$this->get('session')->set( 'fbToken', $accessToken );


					$userFBData		= $service->readUserData( 'me' );

					$fbId			= $userFBData->id;
					$displayName	= isset( $userFBData->name ) ? $userFBData->name : '';

					if( !$displayName ) {
						$displayName	= isset( $userFBData->first_name ) ? $userFBData->first_name : '';
						$displayName	= isset( $userFBData->last_name ) ? ( $displayName ? ' ' : '' ) . $userFBData->last_name : '';
					}
					$email			= isset( $userFBData->email ) ? $userFBData->email : '';
					$url			= isset( $userFBData->link ) ? $userFBData->link : '';

					if( $this->getUserId() ) {
						$user			= $em->getRepository( 'ProtonRigbagBundle:User')->find( $this->getUserId() );
					} else {
						$user			= $em->getRepository( 'ProtonRigbagBundle:User')->findOneBy( array( 'facebook_id' => $fbId ) );
					}

					if( !$user ) {

						//if( !is_null( $promoCode ) ) {

							if( $email ) {
								$user		= $em->getRepository( 'ProtonRigbagBundle:User')->findOneBy( array( 'email' => $email ) );
							} else {
								$user		= null;
							}

							if( !$user ) {
								// CREATE USER
								$user		= new User();

								$user->setName( $displayName )
										->setEmail( $email )
										->setLocation('')
										->setAccountType( 'free' )
										->setState('enabled')
                                                                                ->setPaymentMode(1)
										->setFacebookId( $fbId );

								$em->persist( $user );
							} else {
								$user->setFacebookId( $fbId );
							}
						//}

					} else {
						if( !$user->getFacebookId() ) {
							$user->setFacebookId( $fbId );
						}
					}

					if( $user && !$user->getProfilePicture() ) {
						$profilePicData	= $service->generateUserProfilePictureUrl( 'me' );
					}

					$em->flush();
				}
				else {
					echo 'Upssss';
				}
			break;
		}

		// PROMO CODE
		if( !is_null( $promoCode ) ) {
			$promoCode		= $em->getRepository( 'ProtonRigbagBundle:PromoCode' )->find( $promoCode );

			if( $promoCode->getToRemove() ) {
				$em->remove( $promoCode );
			}
		}


		if( $user && $user->getId() && !is_null( $profilePicData ) )
		{

			$fileContentSrc	= file_get_contents( $profilePicData );

			$sizes			= array( array( 36, 36 ), array( 60, 60 ), array( 100, 100 ), array( 80, 80 ), array( 40, 40 ), array( 160, 160 ), array( 50, 50 ) );

			$paths			= $this->container->getParameter( 'paths' );
			$path			= $paths['storage']['tmp'];

			$confAzure			= $this->container->getParameter( 'azure' );
			$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

			//$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

			// UPLOAD NEW
			$fileTpl	= $paths['storage']['avatar'] . $user->getId() . '_' . time() . '-%size%.jpg';
			$user->setProfilePicture( $fileTpl );

//			$blobOptions	= new CreateBlobOptions();
//			$blobOptions->setContentType( 'image/jpeg' );

			$em->flush();

			$orgPath		= str_replace( '%size%', 'org', $fileTpl );

			$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'adaptiveResize' =>false), true );
			$image->setFormat( 'JPG' );
			$fileContent	= $image->getImageAsString();

			//$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );

			foreach( $sizes as $size ) {

				$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'resizeUp' => true ), true );
				$image->adaptiveResize( $size[0], $size[1] );

				$fPath		= str_replace( '%size%', $size[0] . 'x' . $size[1], $fileTpl );
				$fPath		= str_replace( '%ext%', 'jpg', $fPath );

				$image->setFormat( 'JPG' );

				//$blobProxy->createBlockBlob( $confAzure['storage']['container'], $fPath, $image->getImageAsString(), $blobOptions );
			}
			$em->flush();
		}

		// UPDATE TOKENS
		if( $user && $this->get('session')->get( 'fbToken', null ) ) {
			$user->setFacebookToken( $this->get('session')->get('fbToken') );
		}

		if( $user && $this->get('session')->get( 'twToken', null ) ) {
			$user->setTwitterToken( $this->get('session')->get('twToken') );
		}

		if( $user && $this->get('session')->get( 'gpToken', null ) ) {
			$user->setGoogleToken( $this->get('session')->get('gpToken') );
		}

		$em->flush();

		$redirectTo	= null;

		if( $user && $user->getId() )
		{
			/**
			 * 	1 - adverts
			 *  2 - settings (profile)
			 *  3 - settings (sports)
			 *  4 - settings (subscription)
			 */
			$redirectTo		= 1;
			$dataFilled		= true;
			$profileFilled	= $user->getOptionValue( 'profile_filled' );

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

			$this->get('session')->set( 'dataFilled', ( $dataFilled ? 1 : 0 ) );

			if( $user && !$dataFilled ) {
				$this->get('session')->set('userId', $user->getId() );
			}
		}

		$backUrl		= null;

		switch( $redirectTo ) {
			case 1:
				$userData		= array(
										'id'			=> $user->getId(),
										'description'	=> $user->getName()
									);

				$this->loginUser( $userData );

				$backUrlPath		= $this->get('session')->get('backUrlPath', null);
				$this->get('session')->set('backUrlPath', null);

			break;
			case 2:
				$backUrlPath	= '#/signup/profile/';
			break;
			case 3:
				$backUrlPath	= '#/signup/mysports/';
			break;
			case 4:
				$backUrlPath	= '#/signup/subscription/';
			break;
			default:
				$backUrlPath	= $this->get('session')->get( 'backUrlPath', '' );
				$this->get('session')->get( 'backUrlPath', null );

		}
		if( !isset( $backUrlPath ) ) {
			$backUrlPath	= '#/profile/';
		}
		if( !$user ) {
			$backUrlPath	= '#';
		}
		if( is_null( $backUrl ) ) {
			$backUrl		= $this->generateUrl( 'start', array() ) . $backUrlPath;
		}

// 		// LOGIN TO OTHER NETWORKS
// 		if( $user->getFacebookId() && !$this->get( 'session' )->get( 'fbToken' ) ) {
// 			$backUrl	= $this->generateUrl( 'relogin_facebook', array( 'userId' => $user->getId() ) );
// 			echo("<script> top.location.href='" . $this->getHost() . $backUrl . "'</script>");
// 			exit();
// 		}

// 		if( $user->getTwitterId() && !$this->get( 'session' )->get( 'twToken' ) ) {
// 			echo("<script> top.location.href='" . $this->getHost() . $backUrl . "'</script>");
// 			exit();
// 		}

// 		if( $user->getGoogleId() && !$this->get( 'session' )->get( 'gpToken' ) ) {
// 			echo("<script> top.location.href='" . $this->getHost() . $backUrl . "'</script>");
// 			exit();
// 		}

		return $this->redirect( $backUrl );

	}

    public function autologinAction( Request $request )
    {
		$this->setupLocale($request);
    	$em 	= $this->getDoctrine()->getManager();
    	$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $request->get( 'id', 14) );


        $userData		= array(
									'id'			=> $user->getId(),
									'description'	=> $user->getName()
								);


		$this->loginUser( $userData );

    	$this->get('session')->set('userId', 1);

		return $this->redirect( $this->generateUrl( 'start', array() ) );
    }

    public function logoutAction() {

    	$this->logoutUser();

    	return $this->redirect( $this->generateUrl( 'start', array() ) );
    }

    public function viewGuestAdvertAction( $hash ) {

    	$em 	= $this->getDoctrine()->getManager();
    	$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->findOneBy( array( 'hash' => $hash ) );

    	if( !$advert ) {
    		return $this->redirect( $this->generateUrl( 'start', array( ) ) );
    	}

    	$this->get('session')->set( 'metaTags', array( 'type' => 'advert', 'id' => $advert->getId() ) );

    	return $this->redirect( $this->generateUrl( 'start' ) . '#/adverts/view/' . $advert->getHash() . '/' );


    }
}
