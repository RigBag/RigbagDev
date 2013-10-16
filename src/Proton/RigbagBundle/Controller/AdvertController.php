<?php

namespace Proton\RigbagBundle\Controller;



use WindowsAzure\Blob\Models\CreateBlobOptions;

use Proton\RigbagBundle\Entity\LateAction;

use Proton\RigbagBundle\Entity\FacebookAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\TmpUpload;
use Proton\RigbagBundle\Entity\Advert;
use Proton\RigbagBundle\Entity\AdvertImage;
use Proton\RigbagBundle\Entity\User;
use Proton\RigbagBundle\Entity\Transaction;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Common\Blob;

class AdvertController extends \ProtonLabs_Controller
{

	public function shortUrlAction( $hash ) {

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

		if( !$user ) {
			$user = new User();
		}


		$config			= $this->container->getParameter( 'social' );

		$ogpObject	= new \ProtonLabs_Facebook_OGP_Object();

		$ogpObject->setAppId( $config['facebook']['application_id'] );
		$ogpObject->addNamespace( 'rigbag-com: http://ogp.me/ns/fb/rigbag-com#' );
		$ogpObject->setSiteName( 'RigBag');
		$ogpObject->setTitle( 'RigBag . Powering Action Sports' );
		$ogpObject->setLocale( 'en_US' );
		$rendered	= false;

		$advertView		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->findOneBy( array( 'hash' => $hash ) );

		$extraData	= array(
				'type' 	=> 'advertPrivate',
				'data'	=> array( 'id' => $advertView->getId() )
		);

		if( $advertView && ( $advertView->getState() == 'enabled' || $advertView->getUserId() == $this->getUserId() ) ) {

			$ownAdvert = false;
			if( $user && $advertView->getUserId() == $user->getId() ) {
				$ownAdvert = true;
			}


			if( $user ) {
				$questions		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $advertView->getId(), array( 'ownAdvert' => $ownAdvert, 'userId' => $user->getId() ) );
			} else {
				$questions		= array();
			}
			$hasAccepted	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $advertView->getId() );

			if( $ownAdvert )
			{
				// select as readed
				foreach( $questions as $q ) {
					$q->setReaded( 1 );
				}

				$sysMessages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findBy( array( 'advert_id' => $advertView->getId(), 'state' => 'system' ) );

				foreach( $sysMessages as $m ) {
					$m->setReaded( 1 );
				}

				$em->flush();
			}

			$ogpObject->setDescription( $advertView->getDescription() . ', price: ' . $advertView->getPrice() . strtoupper( $advertView->getCurrency() ) );
			$ogpObject->setUrl( $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advertView->getHash() ) ) );
			$ogpObject->setType( 'rigbag-com:advert' );
			$ogpObject->addOther( array( 'rigbag-com:mode' => $advertView->getMode() ) );
			$ogpObject->setTitle( $advertView->getTitle() );
			$images		= $advertView->getImages();

			$mediaUrl = $this->container->getParameter('azure.storage.url');

			foreach( $images as $image ) {

				$imageUrl = $mediaUrl . str_replace('%size%', '440x380', $image->getPath());

				$ogpObject->addImage( array(
						'url' 		=> $imageUrl,
						'width' 	=> 440,
						'height'	=> 380
				) );
			}
		}
		else {
			return $this->redirect( $this->generateUrl( 'start' ) );
		}

		$isLoged 	= is_null( $isLoged ) ? false : $isLoged;
		$userId		= is_null( $userId ) ? 0 : $userId;

		return $this->render('ProtonRigbagBundle:Main:index.html.twig', array( 'isTablet' => $this->isTablet(),  'extraData' => $extraData, 'advertView' => $advertView, 'hasAccepted' => $hasAccepted, 'questions' => $questions, 'lockInit' => true, 'ogpObject' => $ogpObject, 'bodyClass' => 'advert-single', 'user' => $user, 'isLoged' => $isLoged, 'userId' => $userId ) );

	}

	public function deleteAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$advertId	= $request->get( 'advertId', 0 );
			$em 		= $this->getDoctrine()->getManager();
			$result		= array( 'success' => false );

			if( $advertId )
			{
				$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

				if( $advert->getUserId() == $this->getUserId() )
				{
					$em->remove( $advert );
					$em->flush();
					$result		= array( 'success' => true, 'mode' => $advert->getMode() );
				}
			}

			return new JsonResponse( $result, 200 );
		}
		return $this->blackholeResponse();
	}

	public function closeAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$advertId	= $request->get( 'advertId', 0 );
			$em 		= $this->getDoctrine()->getManager();

			if( $advertId )
			{
				$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

				if( $advert->getUserId() == $this->getUserId() )
				{
					$advert->setState( 'closed' );
					$em->flush();
					$result		= array( 'success' => true, 'hash' => $advert->getHash() );
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function searchAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$query		= $request->get( 'query', '' );
			$sportId	= $request->get( 'category', 0 );
			$mode		= $request->get( 'mode', '' );
			$type		= $request->get( 'type', 'main' );
			$em 		= $this->getDoctrine()->getManager();
			$result		= array();
			$circlesIds	= array();

			if( $this->isLoged() ) {
				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
				$tmp		= $user->getCircles();
			} else {
				$user		= new User();
				$tmp		= $em->getRepository( 'ProtonRigbagBundle:Circle')->findBy( array( 'interest_id' => $sportId ) );
			}

			foreach( $tmp as $t ) {

                           if( $t->getInterestId() == $sportId ) {
					$circlesIds[]	= $t->getId();
				}
                              
			}
  

			$searchParams	= array( 'mode' => $mode );

			if( count( $circlesIds ) ) {
				$searchParams['circles'] = $circlesIds;
			}



			if( $type == 'circle' ) {
				$searchParams['limit']	= 18;
			} else {
				$searchParams['limit']	= 24;
			}
			$searchParams['offset']	= 0;

			$engine 	= $this->container->get('templating');
			$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( $query, $searchParams );

			switch( $type ) {
				case 'circle':
					$result		= array(
							'toUpdate'	=> array( 'circleContent' ),
							'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-content.html.twig', array( 'adverts' => $adverts, 'showLoadMore' => 0 ) )
					);
				break;
				default:
					$interests		= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findAll();

					$result		= array(
							'toUpdate'	=> array( 'content'),
							'content'	=> $engine->render( 'ProtonRigbagBundle:Advert:list-content.html.twig', array( 'user' => $user, 'showLoadMore' => 0, 'adverts' => $adverts ) )
					);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function buyAction( $advertId, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$advertId	= $request->get( 'advertId', 0 );
			$em			= $this->getDoctrine()->getManager();
			$result		= array( 'success' => 0 );

			if( $advertId )
			{
				$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

				if( $advert )
				{
					if( $advert->getState() == 'enabled' ) {

						$advert->setState( 'during_deal' );

						$userSourceId		= $this->getUserId();
						$userDestinationId	= $advert->getUserId();
						$advertId			= $advert->getId();
						$amount				= $advert->getPrice();
						$currency			= $advert->getCurrency();
						$type				= 'advert';
						$method				= 'paypal';
						$state				= '200';

						$userSource			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userSourceId );
						$userDestination	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userDestinationId );

						$description			= \Proton\RigbagBundle\Entity\Transaction::prepareDescription(
															$userSource,
															$userDestination,
															$advert,
															$amount,
															$currency,
															$type,
															$method,
															$state
													);

						$transaction			= new Transaction();

						$transaction->setAdvert( $advert )
									->setFromUser( $userSource )
									->setToUser( $userDestination )
									->setAmount( $amount )
									->setCurrency( $currency )
									->setType( $type )
									->setMethod( $method )
									->setState( $state )
									->setDescription( $description );

						$em->persist( $transaction );


						$em->flush();


						$result['success'] = 1;
						$result['transaction'] = $transaction->getId();
					}
				}
			}
			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}



	public function listAction( $mode, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$listParams	= array(
							'offset'	=> 0,
							'pageSize'	=> 20
						);

			$hideLabel	= true;
			$this->get( 'session' )->set( 'hideLabel', $hideLabel );


			$em 		= $this->getDoctrine()->getManager();

// 			if( $this->isLoged() ) {
// 				$userId		= $this->getUserId();
// 				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
// 			}

			$result		= array();
			$engine 	= $this->container->get( 'templating' );

			$data		= $this->loadList( $mode, array( 'offset' => 0, 'pageSize' => ( $listParams['pageSize'] * 2 ) ) );

			extract( $data );

			$interests	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findAll();



			$result		= array(
					'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass', 'headerExtras' ),
					'header'	=> array(
							'top'		=> $engine->render( 'ProtonRigbagBundle:Advert:view-header-top.html.twig', array( ) ),
							'bottom'	=> $engine->render( 'ProtonRigbagBundle:Advert:view-header-bottom.html.twig', array( 'mode' => $mode, 'interests' => $interests ) ),
							'extras'	=> ''
					),
					'bodyClass'	=> 'adverts',
					'content'	=> $engine->render( 'ProtonRigbagBundle:Advert:list-content.html.twig', array( 'hideLabel' => $hideLabel, 'adverts' => $adverts, 'user' => $user, 'showLoadMore' => ( count( $adverts ) == ( $listParams['pageSize'] * 2 ) ? true : false  ) ) )
			);

			$listParams['type']		= 'list';
			$listParams['mode']		= $mode;
			$listParams['offset']	= $listParams['pageSize'] * 2;
			$this->get( 'session' )->set( 'listParams', $listParams );

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			$response	= new JsonResponse( $result );


			return $response;
		}

		return $this->blackholeResponse();

	}

	public function loadMoreAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$listParams	= $this->get( 'session' )->get( 'listParams' );
			$engine 	= $this->container->get( 'templating' );


			$hideLabel	= $this->get( 'session' )->get( 'hideLabel', false );

			switch( $listParams['type'] ) {

				case 'list':
					$data					= $this->loadList( $listParams['mode'], $listParams );
					extract( $data );
					$listParams['offset']	= $listParams['offset'] + $listParams['pageSize'];
					$this->get( 'session' )->set( 'listParams', $listParams );

					if( count( $adverts ) == $listParams['pageSize'] ) {
						$result['full']	= 1;
					} else {
						$result['full']	= 0;
					}

					$result['content']	= $engine->render( 'ProtonRigbagBundle:Advert:list-more.html.twig', array( 'adverts' => $adverts, 'user' => $user ) );
				break;
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}


	protected function loadList( $mode, $params ) {

		$em 		= $this->getDoctrine()->getManager();

		if( $this->isLoged() ) {
			$userId		= $this->getUserId();
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
		} else {
			$user				= new User();
		}
// 			$circles	= $em->getRepository( 'ProtonRigbagBundle:Circle' )->findForInterests( $user->getInterests() );

// 			$circlesIds	= array();
// 			foreach( $circles as $circle ) {
// 				$circlesIds[]	= $circle->getId();
// 			}


// 			$queryParams		= array( 'mode' => $mode, 'circles' => $circlesIds, 'limit' => $params['pageSize'], 'offset' => $params['offset'] );
// 		} else {

			$queryParams		= array( 'mode' => $mode, 'limit' => $params['pageSize'], 'offset' => $params['offset'] );
// 		}

		$adverts			= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( '', $queryParams );


		return array( 'user' => $user, 'adverts' => $adverts );
	}

	protected function view( $advertId, $idType, Request $request ) {

		$em 		= $this->getDoctrine()->getManager();

		$result		= array();
		$engine 	= $this->container->get('templating');

		if( $idType == 'i' ) {
			$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert')->find( $advertId );
		} elseif ( $idType == 'h' ) {
			$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert')->findOneBy( array( 'hash' => $advertId ) );
		}

		if( $this->isLoged() ) {
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
		} else {
			$user		= new User();
		}

		$flashMessage	= $this->get('session')->get('flashMessage', null );
		$this->get('session')->set( 'flashMessage', null );

		$extraData	= array(
				'type' 	=> 'advertPrivate',
				'data'	=> array( 'id' => $advert->getId() )
		);

		$ownAdvert = false;
		if( $advert->getUserId() == $user->getId() ) {
			$ownAdvert = true;
		}

                $payment_mode = $em->getRepository( 'ProtonRigbagBundle:User' )->PaymentMode( $advert->getUserId() );
		$questions		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $advert->getId(), array( 'ownAdvert' => $ownAdvert, 'userId' => $user->getId() ) );
		$hasAccepted	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $advert->getId() );

		if( $ownAdvert )
		{
			// select as readed
			foreach( $questions as $q ) {
				$q->setReaded( 1 );
			}

			$sysMessages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findBy( array( 'advert_id' => $advert->getId(), 'state' => 'system' ) );

			foreach( $sysMessages as $m ) {
				$m->setReaded( 1 );
			}


			$em->flush();
		} else {

			// select as readed
			foreach( $questions as $q ) {
				$answers = $q->getAnswers();
				foreach( $answers as $a ) {
					$a->setReaded( 1 );
				}
			}
			$em->flush();
		}

		$result		= array(
				'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass', 'headerExtras', 'headerExtras-2', 'flashMessage' ),
				'header'	=> array(
						'top'		=> $engine->render( 'ProtonRigbagBundle:Advert:view-header-top.html.twig', array( ) ),
						'bottom'	=> '', //$engine->render( 'ProtonRigbagBundle:Advert:view-header-bottom.html.twig', array( 'mode' => $advert->getMode() ) ),
						'extras'	=> $engine->render( 'ProtonRigbagBundle:Qa:new-form-header.html.twig', array( 'circles' => false, 'extraData' => $extraData, 'locations' => false ) ),
						'extras2'	=> $engine->render( 'ProtonRigbagBundle:Advert:header-to-friend-form.html.twig', array( 'advert' => $advert ) ),
				),
				'flashMessage'	=> $flashMessage,
				'bodyClass'	=> 'advert-single',
				'content'	=> $engine->render( 'ProtonRigbagBundle:Advert:view-content.html.twig', array( 'advert' => $advert, 'user' => $user, 'questions' => $questions, 'hasAccepted' => $hasAccepted,'payment_mode'=>$payment_mode ) )
		);

		$result['actionStamp']		= $request->get( 'actionStamp', '' );

		return $result;

	}

	protected function canUserView( $advert ) {

		$canView = false;

		if( $advert->getState() == 'enabled' || $advert->getUserId() == $this->getUserId() ) {
			return true;
		}
		else {
			$em 			= $this->getDoctrine()->getManager();

			$transaction = $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findOneBy( array( 'advert_id' => $advert->getId(), 'from_user_id' => $this->getUserId(), 'state' => 'processing' ) );
			if( $transaction ) {
				return true;
			}

			$transaction = $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findOneBy( array( 'advert_id' => $advert->getId(), 'from_user_id' => $this->getUserId(), 'state' => 'completed' ) );
			if( $transaction ) {
				return true;
			}
		}


		return $canView;
	}

	public function viewAction( $advertId, $idType, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{

				$em 		= $this->getDoctrine()->getManager();


				if( $idType == 'i' ) {
					$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert')->find( $advertId );
				} elseif ( $idType == 'h' ) {
					$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert')->findOneBy( array( 'hash' => $advertId ) );
				}

				if( $this->canUserView( $advert ) ) {
					$result	= $this->view( $advertId, $idType, $request );
				} else {
					$result = array( 
									'toUpdate'			=> array( 'flashMessage' ),
									'flashMessage'		=> array(
															'title'		=> '',
															'content'	=> 'Advert doesn\'t exist',
															'type'		=> 'error'
									)
							);
					$result['actionStamp']		= $request->get( 'actionStamp', '' );
				}


			return new JsonResponse( $result );
		}

		return $this->blackholeResponse();
	}

	public function saveAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();
			$response	= new Response();
			$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );
			$result		= array();
			$isUpdate 	= false;

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$condition	= $em->getRepository( 'ProtonRigbagBundle:DictionaryValue' )->find( $request->get( 'condition' ) );


			$advertId	= $request->get( 'id', 0 );

			if( $advertId ) {
				$isUpdate = true;
				$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
				if( $advert->getUserId() != $this->getUserId() ) {
					exit();
				}
			} else {
				$advert		= new Advert();

				$advert->setUser( $user )
							->setMode( $request->get( 'mode' ) )
							->setState( 'enabled' );
			}

			$advert->setTitle( $request->get( 'title' ) )
					->setCondition( $condition )
					->setLocation( $request->get( 'location' ) )
					->setPrice( $request->get( 'price' ) )
					->setSwapFor( $request->get( 'swapFor' ) )
					->setLocationFormated( $request->get( 'locationFormated' ) )
					->setLocationLng( $request->get( 'locationLng' ) )
					->setLocationLat( $request->get( 'locationLat' ) )
					->setPaypalId( $request->get( 'payPal' ) )
					->setCurrency( $request->get( 'currency' ) )
                                        ->setLink( $request->get( 'link' ) );

			$paths			= $this->container->getParameter( 'paths' );

			$confAzure			= $this->container->getParameter( 'azure' );
			$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

			$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

			$sizes				= array( array( 800, 600 ), array( 220, 190 ), array( 440, 380 ), array( 80, 69 ), array( 50, 50 ), array( 80, 80 ), array( 60, 60 ), array( 173, 149 ), array( 60, 51 ), array( 340, 294 ), array( 160, 160 ) );

			$currentImages	= $advert->getImages();
			foreach( $currentImages as $image ) {
				try {
					$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', 'org', $image->getPath() ) );
					foreach( $sizes as $size ) {
						$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', $size[0] . 'x' . $size[1], $image->getPath() ) );
					}

				} catch( ServiceException $e ) {}
				$em->remove( $image );
			}


			$isMain	= '1';
			for( $a = 1; $a < 6; $a++ ) {

				$tmp	= $em->getRepository( 'ProtonRigbagBundle:TmpUpload' )->findOneBy( array( 'session_key' => $this->get('session')->getId(), 'type' => 'advert_photo_' . $a ) );

				if( $tmp ) {

					$advertImage	= new AdvertImage();

					$advertImage->setPath('');
					$advertImage->setAdvert( $advert );
					$advertImage->setIsMain( $isMain );
					$advert->addImage( $advertImage );

					$em->flush();

					$fileTpl		= $paths['storage']['advert'] . $this->getUserId() . '-' . time() . $a . '-%size%.jpg';
					$pathRead		= $paths['storage']['tmp'] . $tmp->getPath();

					$advertImage->setPath( $fileTpl );

					$blobOptions	= new CreateBlobOptions();
					$blobOptions->setContentType( 'image/jpeg' );

					$orgPath		= str_replace( '%size%', 'org', $fileTpl );

					$fileContentSrc	= file_get_contents( $pathRead );
					$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'adaptiveResize' =>false), true );
					$image->setFormat( 'JPG' );
					$fileContent	= $image->getImageAsString();

					$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );

					foreach( $sizes as $size ) {

						$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'resizeUp' => true ), true );
						$image->adaptiveResize( $size[0], $size[1] );

						$fPath		= str_replace( '%size%', $size[0] . 'x' . $size[1], $fileTpl );

						$image->setFormat( 'JPG' );

						$blobProxy->createBlockBlob( $confAzure['storage']['container'], $fPath, $image->getImageAsString(), $blobOptions );
					}



					$isMain	= '0';
					unlink( $pathRead );
					$em->remove( $tmp );


				}

			}

			$circles		= $request->get( 'circles', array() );
			$usedCircles	= array();



			if( $advertId ) {

				$currentCircles	= $advert->getCircles();

				foreach( $currentCircles as $currentCircle ) {
					$usedCircles[]	= $currentCircle->getId();

					if( !in_array( $currentCircle->getId(), $circles ) ) {
						$advert->removeCircle( $currentCircle );
					}
				}

			} else {
// 				if( $user->getAccountType() == 'free' && $advert->getMode() == 'sale' ) {
// 					$result['redirectToPay']	= 1;
// 					$advert->setState( 'waiting_for_payment' );
// 				} else {
					$advert->setState( 'enabled' );
// 				}
				$advert->setHash( $advert->getHash() );
			}

			foreach( $circles as $circleId ) {
				if( !in_array( $circleId, $usedCircles ) ) {
					$circle	= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
					$advert->addCircle( $circle );
				}
			}

			if( !$advertId ) {
				$em->persist( $advert );
			}

			$em->flush();

			$result['advertId']		= $advert->getId();

			$social					= $request->get( 'social', array() );

			if( $advert->getState() == 'enabled' ) {
				$this->socialPublish( $social, $advert, $user, $user );
			} else {
				foreach( $social as $sService ) {

					$lateAction	= new LateAction();

					$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

					switch( $sService ) {
						case 'facebook':
							$lateAction->setUser( $user )
										->setExpiredAt( new \DateTime( '@' . ( time() + 7200 ) ) )
										->setActionType( 'post_advert_facebook' )
										->setActionParams( serialize( array( 'advertId' => $advert->getId(), 'token' => $user->getFacebookToken() ) ) );

							$em->persist( $lateAction );
						break;
						case 'twitter':
							$lateAction->setUser( $user )
										->setExpiredAt( new \DateTime( '@' . ( time() + 7200 ) ) )
										->setActionType( 'post_advert_twitter' )
										->setActionParams( serialize( array( 'advertId' => $advert->getId(), 'token' => $user->getTwitterToken() ) ) );

							$em->persist( $lateAction );
						break;
					}
 				}
 				$em->flush();
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function socialPublishAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();

			$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $request->get( 'advertId' ) );

			if( $advert ) {
				$social	= $request->get( 'social', array() );
				$user	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

				$this->socialPublish( $social, $advert, $user, $user, 'recommend' );

				$result['flashMessage']	= array(
						'title'		=> '',
						'content'	=> 'You have just shared this advert.',
						'type'		=> 'info'
				);
			}
			else {
				$result['flashMessage']	= array(
											'title'		=> '',
											'content'	=> 'Something gone wrong. Please try again.',
											'type'		=> 'error'
											);
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	protected function socialPublish( $social, $advert, $user, $createdBy = null, $action = 'publish' ) {

		$em 	= $this->getDoctrine()->getManager();

		if( !is_array( $social ) ) {
			$social = array();
		}

		$config		= $this->container->getParameter( 'social' );

		foreach( $social as $socialKey ) {

			switch( $socialKey ) {
				case 'facebook':
					$service	= new \ProtonLabs_Facebook_Service(
							$config['facebook']['application_id'],
							$config['facebook']['application_secret'],
							$config['facebook']['scope']
					);

					$service->setAccessToken( $this->get( 'session' )->get( 'fbToken' ) );

					$response = json_decode( $service->createAction( 'me', 'rigbag-com:' . $action, array( 'advert' => $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advert->getHash() ) ) ) ) );


					if( is_object( $response ) && isset( $response->id ) ) {

						$facebookAction	= new FacebookAction();

						$facebookAction->setAdvert( $advert )
										->setFacebookId( $response->id )
										->setFromApp( 0 )
										->setType( $action )
										->setUser( $user );

						if( !is_null( $createdBy ) ) {
							$facebookAction->setCreated( $createdBy );
						}

						$em->persist( $facebookAction );
						$em->flush();
					} else {

					}
				break;
				case 'google':

				break;
				case 'twitter':

					if( $this->get( 'session' )->get( 'twToken' ) ) {

						$tmhOAuth = new \TmhOAuth_Main(array(
								'consumer_key' 		=> $config['twitter']['consumer_key'],
								'consumer_secret' 	=> $config['twitter']['consumer_secret'],
								'curl_ssl_verifypeer'   => false
						));

						$twToken	= $this->get('session')->get( 'twToken' );

						$tmhOAuth->config['user_token'] 	= $twToken['oauth_token'];
						$tmhOAuth->config['user_secret'] 	= $twToken['oauth_token_secret'];




						$params['status']	= 	$advert->getDescription( 'tweet' );
						$params['status']	.=	' ' . $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advert->getHash() ) );

						$response = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update.json'), $params );



// 						if ($code == 200) {
// 							$resp = json_decode($tmhOAuth->response['response']);

// 						} else {
// 							$tmhOAuth->outputError($tmhOAuth);
// 						}
					} else {

					}

				break;
			}

		}

	}

	public function editAction( $advertId, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();

			$result		= array();

			$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

			if( $advert->getUserId() == $this->getUserId() )
			{
				for( $a = 1; $a < 6; $a++ ) {
					$images		= $em->getRepository( 'ProtonRigbagBundle:TmpUpload')->findBy( array( 'type' => 'advert_photo_' . $a, 'session_key' => $this->get( 'session' )->getId() ) );
					if( $images) {
						foreach( $images as $image ) {
							$em->remove( $image );
						}
					}
				}

				$selCircles	= array();
				$circlesA	= $advert->getCircles();
				foreach( $circlesA as $c ) {
					$selCircles[]	= $c->getId();
				}


				$engine 	= $this->container->get('templating');

				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
				$dictionary	= $em->getRepository( 'ProtonRigbagBundle:Dictionary' )->findOneBy( array( 'code' => 'item_condition' ) );
				$conditions	= $em->getRepository( 'ProtonRigbagBundle:DictionaryValue' )->findForDictionary( $dictionary );
				$currencies	= \Proton\RigbagBundle\Entity\Transaction::getCurrencies();
				$circles	= $user->getCircles();

				$actualCondition	= $advert->getCondition();
				$actualCurrency		= \Proton\RigbagBundle\Entity\Transaction::getCurrencyByCode( $advert->getCurrency() );

				if( !is_array( $selCircles ) || !count( $selCircles ) ) {
					$selCircles	= array();
					foreach( $circles as $c ) {
						$selCircles[]	= $c->getId();
					}
				}

				$exPhotos	= array();
				$emPhotos	= array( 1, 2, 3, 4, 5 );
				$emPhotos	= array_reverse( $emPhotos );
				$tmp		= $advert->getImages();
				$lp			= 1;
				$paths		= $this->container->getParameter( 'paths' );
				$path		= $paths['storage']['tmp'];

				$confAzure			= $this->container->getParameter( 'azure' );
				$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

				$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

				foreach( $tmp as $t ) {
					array_pop( $emPhotos );
					$exPhotos[]	= array(
									'id'		=> $t->getId(),
									'lp'		=> $lp,
									'path'		=> $t->getPath()
								);
					$t1				= 'advert_photo_' . $lp;

					$tmpUpload		= new TmpUpload();
					$tmpUpload->setPath( str_replace( '-%size%', '', $t->getPath() ) );
					$tmpUpload->setSessionKey( $this->get( 'session' )->getId() );
					$tmpUpload->setType( $t1 );

					$dir		= pathinfo( $path . $t->getPath() );
					if( !is_dir($dir['dirname'])) {
						mkdir( $dir['dirname'], 0700, true );
					}

					$name = str_replace( '%size%', 'org', $t->getPath() );

					$fileContent = $blobProxy->getBlob( $confAzure['storage']['container'], $name );

					file_put_contents( $path . $tmpUpload->getPath(), $fileContent->getContentStream() );

					$em->persist( $tmpUpload );

					$lp++;
				}
				$emPhotos	= array_reverse( $emPhotos );

				$em->flush();

				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Advert:new-header-top.html.twig', array( 'isEdit' => true ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Advert:new-header-bottom.html.twig', array( 'mode' => $advert->getMode(), 'lock' => true ) )
						),
						'bodyClass'	=> 'advert-new',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Advert:new-content.html.twig', array(
								'conditions' 		=> $conditions,
								'currencies' 		=> $currencies,
								'actualCondition' 	=> $actualCondition,
								'actualCurrency' 	=> $actualCurrency,
								'user'				=> $user,
								'circles'			=> $circles,
								'selCircles'		=> $selCircles,
								'advert'			=> $advert,
								'emPhotos'			=> $emPhotos,
								'exPhotos'			=> $exPhotos
						) )
				);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

    public function addAction( Request $request ) {

		$this->setupLocale($request);
    	if( $request->isXmlHttpRequest() )
    	{
	    	if( !$this->auth() ) {
	    		return $this->unAuthResponse();
	    	}

	    	$em 	= $this->getDoctrine()->getManager();

	    	$selCircles	= $request->get( 'circles', '' );
	    	if( strlen( $selCircles ) ) {
	    		$selCircles	= explode( ',', $selCircles );
	    	} else {
	    		$selCircles	= null;
	    	}

	    	$exPhotos	= array();
	    	$emPhotos	= array( 1, 2, 3, 4, 5 );

	    	for( $a = 1; $a < 6; $a++ ) {
	    		$image		= $em->getRepository( 'ProtonRigbagBundle:TmpUpload')->findOneBy( array( 'type' => 'advert_photo_' . $a, 'session_key' => $this->get( 'session' )->getId() ) );
	    		if( $image) {
	    			$em->remove( $image );
	    		}
	    	}

	    	$em->flush();

	    	$engine 	= $this->container->get('templating');
	    	$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

	    	$dictionary	= $em->getRepository( 'ProtonRigbagBundle:Dictionary' )->findOneBy( array( 'code' => 'item_condition' ) );
	    	$conditions	= $em->getRepository( 'ProtonRigbagBundle:DictionaryValue' )->findForDictionary( $dictionary );
	    	$currencies	= \Proton\RigbagBundle\Entity\Transaction::getCurrencies();
	    	$circles	= $user->getCircles();
	    	$advert		= new Advert();
	    	$advert->setLocation( $user->getLocation() );
	    	$advert->setLocationFormated(  $user->getLocationFormated() );
	    	$advert->setLocationLat( $user->getLocationLat() );
	    	$advert->setLocationLng( $user->getLocationLng() );
	    	$advert->setPaypalId( $user->getPaypalId() );

	    	$advert->setMode( 'sale' );

	    	foreach( $conditions as $actualCondition ) {
	    		break;
	    	}
	    	foreach( $currencies as $actualCurrency ) {
	    		break;
	    	}

	    	if( !is_array( $selCircles ) || !count( $selCircles ) ) {
	    		$selCircles	= array();
	//     		foreach( $circles as $c ) {
	//     			$selCircles[]	= $c->getId();
	//     		}
	    	}

	    	$result		= array(
	    			'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
	    			'header'	=> array(
	    					'top'		=> $engine->render( 'ProtonRigbagBundle:Advert:new-header-top.html.twig', array( 'isEdit' => false ) ),
	    					'bottom'	=> $engine->render( 'ProtonRigbagBundle:Advert:new-header-bottom.html.twig', array() )
	    			),
	    			'bodyClass'	=> 'advert-new',
	    			'content'	=> $engine->render( 'ProtonRigbagBundle:Advert:new-content.html.twig', array(
	    																									'conditions' 		=> $conditions,
	    																									'currencies' 		=> $currencies,
	    																									'actualCondition' 	=> $actualCondition,
	    																									'actualCurrency' 	=> $actualCurrency,
	    																									'user'				=> $user,
	    																									'circles'			=> $circles,
	    																									'selCircles'		=> $selCircles,
	    																									'advert'			=> $advert,
	    																									'exPhotos'			=> $exPhotos,
	    																									'emPhotos'			=> $emPhotos
	    																								) )
	    	);


	    	$result['actionStamp']		= $request->get( 'actionStamp', '' );

	    	return new JsonResponse( $result, 200 );
    	}

		return $this->blackholeResponse();

    }
}