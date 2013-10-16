<?php

namespace Proton\RigbagBundle\Controller;

use Proton\RigbagBundle\Entity\MobileLog;

use Symfony\Component\HttpFoundation\JsonResponse;

use WindowsAzure\Blob\Models\CreateBlobOptions;

use WindowsAzure\Common\ServicesBuilder;

use Symfony\Component\BrowserKit\Response;

use Proton\RigbagBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Proton\RigbagBundle\Entity\QaPosition;
use Proton\RigbagBundle\Entity\UserOption;
use Proton\RigbagBundle\Entity\Advert;
use Proton\RigbagBundle\Entity\Transaction;
use Proton\RigbagBundle\Entity\Dictionary;
use Proton\RigbagBundle\Entity\DictionaryValue;
use Proton\RigbagBundle\Entity\TmpUpload;
use Proton\RigbagBundle\Entity\AdvertImage;
use Proton\RigbagBundle\Entity\Circle;
use Proton\RigbagBundle\Entity\Location;

class ApiController extends \ProtonLabs_Controller {


	// LIMITS
	protected static $LIMIT_ADVERTS_FIRST 	= 25;
	protected static $LIMIT_ADVERTS_NEXT 	= 15;

	protected static $LIMIT_NEWS_FIRST 		= 30;
	protected static $LIMIT_NEWS_NEXT 		= 20;

	protected static $LIMIT_QA_FIRST 		= 20;
	protected static $LIMIT_QA_NEXT 		= 12;


	
	public function logAction( Request $request ) {
		
		$em = $this->getDoctrine()->getManager();
		
		$userId = $request->get( 'userId', null );
		$model = $request->get( 'model', '' );
		$version = $request->get( 'version', '' );
		$platform = $request->get( 'platform', '' );
		$action = $request->get( 'action', null );
		$dataDump = $request->get( 'dataDump', '' );
		
		if( $userId && $action ) {
		
			$mobileLog = new MobileLog();
			
			if( is_array( $dataDump ) ) {
				$dataDump = serialize( $dataDump ); 
			}
			
			
			$mobileLog->setUserId( $userId )
					->setModel( $model )
					->setVersion( $version )
					->setPlatform( $platform )
					->setAction( $action )
					->setDataDump( $dataDump );
			
			$em->persist( $mobileLog );
			$em->flush();
			
			$response = new JsonResponse( array( 'message' => 'Log saved' ), 200 );
		} 
		else {
			$response = new JsonResponse( array( 'message' => 'Unrecognized request' ), 400 );
		}
		
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}
	
	public function userNotificationsListAction( Request $request ) {

		$em = $this->getDoctrine()->getManager();
		$userId = $request->get( 'userId', null );
		$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
		
		if( $user ) {
			$messages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findMessagesToUser( $userId, 'unreaded' );
			$result['messages'] = array();
			$mediaUrl = $this->container->getParameter('azure.storage.url');
			
			foreach( $messages as $m ) {
				
				$image = '';
				if( $m->getState() == 'system' ) {
					if( $m->getAdvertId() ) {
						$image = $mediaUrl . str_replace('%size%', '50x50', $m->getAdvert()->getMainImage()->getPath());
					}
				} else {
					if( $m->getUserId() ) {
						$image = $m->getUser()->getProfilePicture();
						$image = $mediaUrl . str_replace( '%size%', '80x80', $image );
					}
				}
				
				$info = '';
				
				if( $m->getState() == 'system' ) {
					if( $m->getAdvertId() ) {
						$title = $m->getAdvert()->getTitle();
					} else {
						$title = '#' . $m->getId();
					}
				} else {
					$title = $m->getUser()->getName();
					if( $m->getAdvertId() ) {
						$info = $m->getAdvert()->getTitle();
					}
				}
				
				
				$result['messages'][]		= array(
									'id'			=> $m->getId(),
									'state'			=> $m->getState(),
									'parentId'		=> ( $m->getParentId() ? $m->getParentId() : $m->getId() ),
									'advertId'		=> $m->getAdvertId(),
									'title'			=> $title,
									'createdAt'		=> date( 'd.m.Y H:i:s', $m->getCreatedAt()->getTimestamp() ),
									'addedAgo'		=> $m->getAddedAgo(),
									'body'			=> $m->getBody(),
									'image'			=> $image,
									'info'			=> $info
								);
				
				$m->setReaded( 1 );
			}
			
			$em->flush();
			
			$response = new JsonResponse( $result, 200 );
		}
		else {
			$response = new JsonResponse( array( 'message' => 'User doesn\'t exist' ), 404 );
		}
		
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}
	
	public function userNotificationsInfoAction( Request $request ) {
		
		$em = $this->getDoctrine()->getManager();
		$userId = $request->get( 'userId', null );
		$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
		
		if( $user ) {
			$messages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findMessagesToUser( $userId, 'unreaded' );
			$c			= 0;
			foreach( $messages as $m ) { $c++; }
			$result['newMessagesNum']	= $c;
			$response = new JsonResponse( $result, 200 );
		}
		else {
			$response = new JsonResponse( array( 'message' => 'User doesn\'t exist' ), 404 );
		}
		
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}
	
	public function transactionsListAction( Request $request ) {
		
		$em = $this->getDoctrine()->getManager();
		$userId = $request->get( 'userId', null );
		$result = array( 'success' => 0 );
		
		if( $userId ) {
			
			$result	= array(
				'success'		=> 1,
				'transactions'	=> array()		
			);
			$transactions	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findForUser( $userId );
			
			foreach( $transactions as $transaction ) {
				
				if( $transaction->getAdvertId() ) {
					$advert  = array(
						'id'			=> $transaction->getAdvert()->getId(),
						'title'			=> $transaction->getAdvert()->getTitle()		
					);
				} else {
					$advert = array( 'id'=>0);
				}
				
					$result['transactions'][]	= array(
						'id'			=> $transaction->getId(),
						'label'			=> $transaction->getTypeLabel( $userId ),
						'isIncome'		=> $transaction->isIncome( $userId ),
						'createdAt'		=> date( 'd.m.Y', $transaction->getCreatedAt()->getTimestamp() ),
						'description'	=> $transaction->getDescription(),
						'fromUser'		=> array(
							'id'			=> $transaction->getFromUserId(),
							'name'			=> $transaction->getFromUserName()		
						),
						'toUser'		=> array(
								'id'			=> $transaction->getToUserId(),
								'name'			=> $transaction->getToUserName()
						),
						'advert'		=> $advert,
						'type'			=> $transaction->getType(),
						'state'			=> $transaction->getState(),
						'amount'		=> $transaction->getAmount(),
						'currency'		=> $transaction->getCurrency(),
						'currencyLabel'	=> $transaction->getCurrencyLabel()
					);
				
			}
		}
		
		if( $result['success'] ) {
			$response = new JsonResponse( $result, 200 );
		}
		else {
			$response = new JsonResponse( array(), 400 );
		}
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}
	
	
	// CLEAR TMP
	/************
	 *
	 */
	public function clearTmpAction(Request $request) {

		
		$sessionId	= $request->get( 'sessionId', null );
		$result['success'] = 0;
		
		if( $sessionId ) {
			$em = $this->getDoctrine()->getManager();

			$tmps = $em->getRepository('ProtonRigbagBundle:TmpUpload')->findBy(array('session_key' => $request->get('sessionId')));

			foreach ($tmps as $tmp) {
				$em->remove($tmp);
			}

			$em->flush();

			$result['success'] = 1;
		}

		if( $result['success'] ) {
			$response = new JsonResponse( $result, 200 );
		}
		else {
			$response = new JsonResponse( array(), 400 );
		}
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}

	// NEWS LIST
	/***************
	 *
	 */
	public function newsListAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$result = array();

		$offset = $request->get( 'offset', 0 );

		if( $offset ) {
			$limit = self::$LIMIT_NEWS_NEXT;
		} else {
			$limit = self::$LIMIT_NEWS_FIRST;
		}

		$news = $em->getRepository('ProtonRigbagBundle:News')->findBy(array(), array('add_date' => 'desc'), $limit, $offset );
		$result['news'] = array();

		foreach ($news as $n) {

			$value = $n->getContent();

			$value = preg_replace('@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a data-href="$1" class="openBlank">$1</a>', $value);
			$value = preg_replace('/@(\w+)/', '<a data-href="http://twitter.com/$1" class="openBlank">@$1</a>', $value);
			$value = preg_replace('/\s+#(\w+)/', ' <a data-href="http://twitter.com/search?q=$1" class="openBlank">#$1</a>', $value);

			$result['news'][] = array('id' => $n->getId(), 'addedAgo' => $n->getAddedAgo(), 'content' => $value, 'image' => $n->getTwUserPicture());
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);

	}

	// UPLOAD PHOTO
	/***************
	 *
	 */
	public function uploadPhotoAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$type = $request->get('type', null);
		$sessionId = $request->get('sessionId', null);
		$userId = $request->get('userId', null);
		$paths = $this->container->getParameter('paths');
		$path = $paths['storage']['tmp'] . 'advert/';

		$fileTmp = $_FILES['file']['tmp_name'];
		$fileName = $userId . '_' . $_FILES['file']['name'];

		$subPath = \ProtonLabs_Dir::generatePath();

		\ProtonLabs_Dir::makeDirectory($path . $subPath);

		move_uploaded_file($_FILES['file']['tmp_name'], $path . $subPath . $fileName);


		switch( $type ) {
			case 'avatar':


				$sizes				= array( array( 36, 36 ), array( 60, 60 ), array( 100, 100 ), array( 80, 80 ), array( 40, 40 ), array( 160, 160 ), array( 50, 50 ) );

				$confAzure			= $this->container->getParameter( 'azure' );
				$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

				$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

				// DELETE ALL
				$oldFile	= $user->getProfilePicture();
				try {
					$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', 'org', $oldFile ) );
					foreach( $sizes as $size ) {
						$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', $size[0] . 'x' . $size[1], $oldFile ) );
					}
				}
				catch( ServiceException $e ) {}

				// UPLOAD NEW
				$fileTpl	= $paths['storage']['avatar'] . $user->getId() . '_' . time() . '-%size%.jpg';
				$user->setProfilePicture( $fileTpl );

				$blobOptions	= new CreateBlobOptions();
				$blobOptions->setContentType( 'image/jpeg' );

				$em->flush();

				$orgPath		= str_replace( '%size%', 'org', $fileTpl );

				$fileContentSrc	= file_get_contents( $path . $subPath . $fileName );
				$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'adaptiveResize' =>false), true );
				$image->setFormat( 'JPG' );
				$fileContent	= $image->getImageAsString();

				$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );

				foreach( $sizes as $size ) {

					$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'resizeUp' => true ), true );
					$image->adaptiveResize( $size[0], $size[1] );

					$fPath		= str_replace( '%size%', $size[0] . 'x' . $size[1], $fileTpl );
					$fPath		= str_replace( '%ext%', 'jpg', $fPath );

					$image->setFormat( 'JPG' );

					$blobProxy->createBlockBlob( $confAzure['storage']['container'], $fPath, $image->getImageAsString(), $blobOptions );
				}

				unlink( $path . $subPath . $fileName );



			break;
			default:
				$tmpUpload = new TmpUpload();

				$tmpUpload->setPath($subPath . $fileName);
				$tmpUpload->setSessionKey($sessionId);
				$tmpUpload->setType($type);

				$em->persist($tmpUpload);
		}

		$em->flush();

		return new \Symfony\Component\HttpFoundation\Response('');
	}


	// ADVERT DELETE
	/****************
	 *
	 */
	public function advertDeleteAction( Request $request ) {

		$em = $this->getDoctrine()->getManager();

		$userId = $request->get('userId', null);
		$advertId = $request->get('advertId', null);

		$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->findOneBy( array( 'id' => $advertId ) );

		if( $advert && $advert->getUserId() == $userId ) {
			$em->remove( $advert );
			$em->flush();
			$result['success'] = 1;
		}
		else {
			$result['success'] = 0;
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// ADVERT SAVE
	/***************
	 *
	 */
	public function advertSaveAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$advertData = $request->get('advert', null);
		$userId = $request->get('userId', null);
		$sessionId = $request->get('sessionId', null);

		$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
		$condition = $em->getRepository('ProtonRigbagBundle:DictionaryValue')->find($advertData['condition']);

		if( isset( $advertData['id'] ) &&  $advertData['id'] ) {
			$advertId = $advertData['id'];
		} else {
			$advertId = 0;
		}

		if ($advertId) {
			$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);
			if ($advert->getUserId() != $userId) {
				exit();
			}
		} else {
			$advert = new Advert();

			$advert->setUser($user)->setMode($advertData['mode'])->setState('enabled');
		}

		$advert->setTitle($advertData['title'])
				->setCondition($condition)
				->setLocation($advertData['location']['label'])
				->setPrice($advertData['price']['amount'])
				->setSwapFor($advertData['swapFor'])
				->setLocationFormated($advertData['location']['formated'])
				->setLocationLng($advertData['location']['lng'])
				->setLocationLat($advertData['location']['lat'])
				->setCurrency($advertData['price']['currency'])
				->setPaypalId( $advertData['paypal'] );

		$paths = $this->container->getParameter('paths');

		if( isset( $advertData['oldPhotos'] ) && is_array( $advertData['oldPhotos'] ) ) 
		{
			$oldPhotos 			= $advertData['oldPhotos'];
			$currentImages	= $advert->getImages();
			foreach( $currentImages as $image ) {
				if( !in_array( $image->getId(), $oldPhotos ) ) {
					$em->remove( $image );
				}
			}
		}

		$confAzure			= $this->container->getParameter( 'azure' );
		$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

		$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

		$sizes				= array( array( 800, 600 ), array( 220, 190 ), array( 440, 380 ), array( 80, 69 ), array( 50, 50 ), array( 80, 80 ), array( 60, 60 ), array( 173, 149 ), array( 60, 51 ), array( 340, 294 ), array( 160, 160 ) );

		$isMain = '1';
		$a = 0;
		$tmps = $em->getRepository('ProtonRigbagBundle:TmpUpload')->findBy(array('session_key' => $sessionId));

		foreach ($tmps as $tmp) {

			$advertImage	= new AdvertImage();

			$advertImage->setPath('');
			$advertImage->setAdvert( $advert );
			$advertImage->setIsMain( $isMain );
			$advert->addImage( $advertImage );

			$em->flush();

			$fileTpl		= $paths['storage']['advert'] . $userId . '-' . time() . $a . '-%size%.jpg';
			$pathRead		= $paths['storage']['tmp'] . 'advert/' . $tmp->getPath();

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
			$a++;
			$em->remove( $tmp );
		}

		$circles = $advertData['circles'];
		$usedCircles = array();

		if ($advertId) {

			$currentCircles = $advert->getCircles();

			foreach ($currentCircles as $currentCircle) {
				$usedCircles[] = $currentCircle->getId();

				if (!in_array($currentCircle->getId(), $circles)) {
					$advert->removeCircle($currentCircle);
				}
			}

		} else {
			$advert->setHash($advert->getHash());
		}

		foreach ($circles as $circleId) {
			if (!in_array($circleId, $usedCircles)) {
				$circle = $em->getRepository('ProtonRigbagBundle:Circle')->find($circleId);
				if ($circle) {
					$advert->addCircle($circle);
				}
			}
		}

		if (!$advertId) {
			$em->persist($advert);
		}

		$em->flush();
		
		$result['success'] = 1;
		$result['advertId'] = $advert->getId();
		$result['mode'] = $advert->getMode();
		$result['url'] = $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advert->getHash() ) );
		$result['description'] = stripslashes( $advert->getDescription() );

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// MEMBERS LIST (GET)
	/*********************
	 *
	 */
	public function membersListAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$circleId = $request->get('circleId', null);

		if (!is_null($circleId)) {

			$circle = $em->getRepository('ProtonRigbagBundle:Circle')->find($circleId);
			$users = $circle->getUsers();

		} else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$result['members'] = array();

		foreach ($users as $user) {
			$result['members'][] = $this->getUserSimple($user, 80, 80);
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// CIRCLE (LEAVE)
	/*****************
	 *
	 */
	public function circleLeaveAction( Request $request ) {

		$em = $this->getDoctrine()->getManager();

		$circleId = $request->get('circleId', null);
		$userId = $request->get( 'userId', null );

		$circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
		$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

		if( $circle && $user ) {
			$user->removeCircle( $circle );
			$em->flush();

			$result['success'] = 1;
		}
		else {
			$result['success'] = 0;
		}


		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	// CIRCLE (JOIN)
	/*****************
	 *
	*/
	public function circleJoinAction( Request $request ) {
	
		$em = $this->getDoctrine()->getManager();
	
		$circleId = $request->get('circleId', null);
		$userId = $request->get( 'userId', null );
	
		$circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
		$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
	
		if( $circle && $user ) {
			if (!$user->hasCircle($circle)) {
				$user->addCircle( $circle );
				$em->flush();	
			}	
			$result['success'] = 1;
		}
			else {
			$result['success'] = 0;
		}
	
	
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// CIRCLE (GET)
	/****************
	 *
	 */
	public function circleAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$circleId = $request->get('circleId', null);

		if (!is_null($circleId)) {

			$circle = $em->getRepository('ProtonRigbagBundle:Circle')->find($circleId);

		} else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$result['circle'] = array('id' => $circle->getId(), 'name' => $circle->getName(), 'description' => $circle->getDescription(),
				'image' => array('url' => $this->getHost() . $this->generateUrl('image_sport', array('sportId' => $circle->getInterestId(), 'width' => 60, 'height' => 60)), 'width' => '', 'height' => ''),
				'location' => array('id' => $circle->getLocation()->getId(), 'name' => $circle->getLocation()->getName()), 'interest' => array('id' => $circle->getInterest()->getId(), 'name' => $circle->getInterest()->getName()));

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// CIRCLES BROWSE
	/******************
	 * 
	 */
	public function circlesBrowseAction( Request $request ) {
		
		$em = $this->getDoctrine()->getManager();
		
		$locationId = $request->get( 'locationId', 0 );
		$sportId = $request->get( 'sportId', 0 );
		$init = $request->get( 'init', 0 );
		$userId = $request->get( 'userId', 0 );
		
		$result 	= array(
						'locations'		=> array(),
						'sports'		=> array(),
						'circles'		=> array()
					);
		
		// INIT
		if( $init ) {
			$sports 	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findBy( array(), array( 'name' => 'asc' ) );
			$locations  = $em->getRepository( 'ProtonRigbagBundle:Location' )->findBy( array(), array( 'name' => 'asc' ) );
			$limit		= 30;
		} else {
			$sports 	= array();
			$locations 	= array();
			$limit		= null;
		}
		
		// OTHER CIRCLES
		$crit 		= array();
		if( !is_null( $sportId ) && $sportId ) {
			$crit['interest_id'] = $sportId;
		}
		if( !is_null( $locationId ) && $locationId ) {
			$crit['location_id'] = $locationId;
		}
		$circles = array();
		$user 		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
		$tmp 		= $user->getCircles();
		$uc 		= array();
		foreach( $tmp as $t ) { $uc[] = $t->getId(); };
		$tmp 		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->findBy( $crit, array( 'name' => 'asc', 'description' => 'asc' ), $limit );
		$circles 	= array();
		foreach( $tmp as $t ) {
			if( !in_array( $t->getId(), $uc ) ) {
				$circles[] = $t;
			}
		}

		// PARSE
		foreach( $sports as $sport ) {
			$result['sports'][]	= array(
				'id'	=> $sport->getId(),
				'name'	=> $sport->getName()		
			);
		}
		
		foreach( $locations as $location ) {
			$result['locations'][] = array(
				'id'	=> $location->getId(),
				'name'	=> $location->getName()	
			);
		}
		
		foreach( $circles as $circle ) {
			$result['circles'][]	= array(
				'id' => $circle->getId(), 
				'name' => $circle->getName(), 
				'description' => $circle->getDescription(),
				'image' => array('url' => $this->getHost() . $this->generateUrl('image_sport', array('sportId' => $circle->getInterestId(), 'width' => 60, 'height' => 60)), 'width' => '', 'height' => '')
			);
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	
	// CIRCLES LIST (GET)
	/**********************
	 *
	 */
	public function circlesListAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$userId = $request->get('userId', null);
		$query = $request->get('query', null);

		if (!is_null($userId)) {

			$circlesObj = $em->getRepository('ProtonRigbagBundle:Circle')->findForUser($userId);
			

		} elseif (!is_null($query)) {

		} else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$result['circles'] = array();

		foreach ($circlesObj as $circle) {

			$result['circles'][] = array('id' => $circle->getId(), 'name' => $circle->getName(), 'description' => $circle->getDescription(),
					'image' => array('url' => $this->getHost() . $this->generateUrl('image_sport', array('sportId' => $circle->getInterestId(), 'width' => 60, 'height' => 60)), 'width' => '', 'height' => ''),
					'location' => array('id' => $circle->getLocation()->getId(), 'name' => $circle->getLocation()->getName()), 'interest' => array('id' => $circle->getInterest()->getId(), 'name' => $circle->getInterest()->getName()));

		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// QUESTION VIEW (GET)
	/************************
	 *
	 */
	public function questionViewAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$questionId = $request->get('questionId', null);

		if (!is_null($questionId)) {
			$question = $em->getRepository('ProtonRigbagBundle:QaPosition')->find($questionId);
		} else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$answers = $question->getAnswers();
		$result['messages'] = array();
	
		$result['question'] = array( 'id' => $question->getId(), 'advert' => $this->getAdvertSimple($question->getAdvert()), 'addedAgo' => $question->getAddedAgo(), 'answersNum' => 0, 'content' => stripslashes($question->getBody()), 'user' => $this->getUserSimple($question->getUser(), 60, 60));
		foreach ($answers as $answer) {
			$result['messages'][] = array('id' => $answer->getId(), 'addedAgo' => $answer->getAddedAgo(), 'answersNum' => 0, 'content' => stripslashes($answer->getBody()), 'user' => $this->getUserSimple($answer->getUser(), 60, 60));
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// SEARCH
	/************************
	 *
	 */
	public function searchAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$query = $request->get('query', '');
		$mode = $request->get('mode', 0);
		$type = $request->get('type', null);
		$where = $request->get('where', 'main');
		$whereId = $request->get('whereId', 0);
		$userId = $request->get('userId', 0);
		$actionStamp = $request->get('actionStamp', null );
		$circlesIds = array();

		$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);

		switch ($type) {
		// Adverts
		case 'adverts':

			if( $whereId ) {
				$circlesIds = array( $whereId );
			} else {
// 				$tmp		= $user->getCircles();

// 				foreach( $tmp as $t ) {
// 					$circlesIds[]	= $t->getId();
// 				}
			}


			$searchParams	= array( 'mode' => $mode );
			$searchParams['limit']	= 12;

			if( count( $circlesIds ) ) {
				$searchParams['circles'] = $circlesIds;
			}

			$advertsResult	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( $query, $searchParams );

			$imgWidth = 160;
			$imgHeight = 160;
			$adverts = array();

			$mediaUrl = $this->container->getParameter('azure.storage.url');

			foreach ($advertsResult as $advertObj) {

				$mainImage = $advertObj->getMainImage();

				if ($mainImage) {
					$imageUrl = $mediaUrl . str_replace('%size%', $imgWidth . 'x' . $imgHeight, $mainImage->getPath());

					$confAzure			= $this->container->getParameter( 'azure' );
					$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];
					$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );
					
					if (!$this->msBlobFileExist($imageUrl)) {
						$this->msBlobImageResize($mainImage->getPath(), array('w' => $imgWidth, 'h' => $imgHeight));
					}
				} else {
					$imageUrl = null;
				}

				$advert = array('id' => $advertObj->getId(), 'mode' => $advertObj->getMode(), 'addedAgo' => $advertObj->getAddedAgo(), 'description' => stripslashes($advertObj->getDescription()),
						'price' => array('amount' => $advertObj->getPrice(), 'currency' => ($advertObj->getCurrency() == 'eur' ? '&euro;' : $advertObj->getCurrency())),
						'image' => array('url' => $imageUrl, 'width' => $imgWidth, 'height' => $imgHeight));

				$adverts[] = $advert;
			}

			$result = array('adverts' => $adverts);

			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);


			break;

		// Q&A
		case 'qa':

			if( $whereId ) {
				$circlesIds = array( $whereId );
			} else {
// 				$tmp		= $user->getCircles();

// 				foreach( $tmp as $t ) {
// 					$circlesIds[]	= $t->getId();
// 				}
			}
			$searchParams = array();
			if( count( $circlesIds ) ) {
				$searchParams	= array( 'circles' => $circlesIds );
			}
			$positions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->search( $query, $searchParams );


			$questions = array();

			foreach ($positions as $position) {

				$questions[] = array('id' => $position->getId(), 'addedAgo' => $position->getAddedAgo(), 'answersNum' => $position->getAnswersNum(), 'content' => stripslashes($position->getBody()), 'user' => $this->getUserSimple($position->getUser(), 60, 60),
						'advert' => $this->getAdvertSimple($position->getAdvert()));
			}

			$result = array('messages' => $questions, 'actionStamp' => $actionStamp );

			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);

			break;
		}

	}

	// QUESTION DELETE
	/******************
	 *
	 */
	public function questionDeleteAction( Request $request ) {

		$em = $this->getDoctrine()->getManager();

		$qId 		= $request->get( 'questionId', null );
		$userId 	= $request->get( 'userId', null );

		$question 	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $qId );

		if( $question && $question->getUserId() == $userId ) {

			$parent = $question->getQuestion();

			if( $parent ) {
				$parent->setAnswersNum( $parent->getAnswersNum() - 1 );
			}

			$em->remove( $question );
			$em->flush();
			$result['success'] = 1;
		}
		else {
			$result['success'] = 0;
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// QUESTIONS LIST (GET)
	/************************
	 * mode		: full, own
	 */
	public function questionsListAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$circleId = $request->get('circleId', null);
		$userId = $request->get('userId', null);
		$mode = $request->get('mode', 'full');
		$offset = $request->get( 'offset', 0 );

		if( $offset ) {
			$limit = self::$LIMIT_QA_NEXT;
		}
		else {
			$limit = self::$LIMIT_QA_FIRST;
		}



		// for User Circles
		if (!is_null($userId) && $mode == 'full') {

			$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
			$circles = $user->getCircles();
			$circlesIds = array();

			foreach ($circles as $c) {
				$circlesIds[] = $c->getId();
			}

			$positions = $em->getRepository('ProtonRigbagBundle:QaPosition')->search('', array('circles' => $circlesIds, 'offset' => $offset, 'limit' => $limit));

		}
		// for User (Profile)
 		elseif (!is_null($userId) && $mode == 'own') {

			$positions = $em->getRepository('ProtonRigbagBundle:QaPosition')->search('', array( 'userId'=>$userId, 'offset' => $offset, 'limit' => $limit));
		}
		// by Circle
 		elseif (!is_null($circleId)) {
			$positions = $em->getRepository('ProtonRigbagBundle:QaPosition')->search('', array('circles' => array($circleId), 'offset' => $offset, 'limit' => $limit));
		}
		// Error
 		else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$questions = array();

		foreach ($positions as $position) {

			$questions[] = array('id' => $position->getId(), 'addedAgo' => $position->getAddedAgo(), 'answersNum' => $position->getAnswersNum(), 'content' => stripslashes($position->getBody()), 'user' => $this->getUserSimple($position->getUser(), 60, 60),
					'advert' => $this->getAdvertSimple($position->getAdvert()));
		}

		$result = array('messages' => $questions);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// SUGGEST (POST)
	/********************
	 *
	 */
	public function suggestAction(Request $request) {

		$advertId	= $request->get( 'advertId', null );
		$msg		= $request->get( 'message', null );
		$userId		= $request->get( 'userId', null );
		$emails		= explode( ',', str_replace( "\n", '', $request->get( 'emails', '' ) ) );
		
		$result		= $this->suggestAdvertToFriend( $advertId, $msg, $emails, $userId );
		
		$response 	= new JsonResponse( $result, 200 );
		$callback 	= $request->get( 'callback', null );
		
		if( $callback ) {
			$response->setCallback( $callback );
		}
		
		return $response;

	}

	// ASK SELLER (POST)
	/**********************
	 *
	 */
	public function askSellerAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$message = $request->get('message', null);
		$advertId = $request->get('advertId', null);
		$fromUserId = $request->get('fromUserId', null);

		$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);
		$user = $em->getRepository('ProtonRigbagBundle:User')->find($fromUserId);
		$qa = new QaPosition();

		$qa->setToUser($advert->getUser())->setBody($message)->setAdvert($advert)->setState('private')->setUser($user)->setAnswersNum(0);

		$em->persist($qa);
		$em->flush();
		
		$this->send( 'advertAsk', array( 'advertId' => $advertId, 'qaId' => $qa->getId() ) );

		$result = array('success' => 1);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// SPORT STATE
	/**************
	 *
	 */
	public function sportStateAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$userId = $request->get('userId', null);
		$sportId = $request->get('sportId', null);
		$state = $request->get('state', 0);

		$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
		$sport = $em->getRepository('ProtonRigbagBundle:Interest')->find($sportId);

		$circle = null;
		$location = $em->getRepository( 'ProtonRigbagBundle:Location' )->findOneBy( array( 'code' => $user->getLocationCountryCode() ) );

		if( $location ) {
			$circle = $em->getRepository( 'ProtonRigbagBundle:Circle')->findOneBy( array( 'interest_id' => $sportId, 'location_id' => $location->getId() ) );

			if( !$circle ) {
				$circle = new Circle();
				$circle->setInterest( $sport )
					->setLocation( $location )
					->setName( $sport->getName() )
					->setDescription( $location->getName() );
				$em->persist( $circle );
			}
		}

		if ($state) {
			if (!$user->hasInterest($sport)) {
				$user->addInterest($sport);	
			}
			if ($circle && !$user->hasCircle($circle)) {
				$user->addCircle( $circle );
			}
		} else {
			if ($user->hasInterest($sport)) {
				$user->removeInterest($sport);
			}
			if ($circle && $user->hasCircle($circle)) {
				$user->removeCircle( $circle );
			}
		}

		$em->flush();

		$result = array('success' => 1);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// SET SUBSCRIPTION
	/*******************
	 *
	 */
	public function setSubscriptionAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$userId = $request->get('userId', null);
		$type = $request->get('type', null);

		$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);

		switch ($type) {
		case 'free':
			$user->setAccountType('free');
			$user->setExpiredAt(null);

			$subscriptionFilled = $user->getOptionValue('subscription_filled');
			if (!$subscriptionFilled) {
				$uo = new UserOption();
				$uo->setOptionKey('subscription_filled')->setOptionValue(date('Y-m-d H:i:s'))->setUser($user);

				$user->addOption($uo);

				$em->persist($uo);
			}
			break;
		case 'annual':
			$expDate = new \DateTime();
			$expDate->setTimestamp(time() + (60 * 60 * 24 * 365));

			$user->setAccountType('annual');
			$user->setExpiredAt($expDate);

			$subscriptionFilled = $user->getOptionValue('subscription_filled');
			if (!$subscriptionFilled) {
				$uo = new UserOption();
				$uo->setOptionKey('subscription_filled')->setOptionValue(date('Y-m-d H:i:s'))->setUser($user);

				$user->addOption($uo);

				$em->persist($uo);
			}

			break;
		}

		$em->flush();

		$result = array('success' => 1);
		$result['user'] = $this->userProfile($user);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// GET PROFILE
	/***************
	 *
	 */
	public function getProfileAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$id = $request->get( 'id', null );
		$name = $request->get( 'name', '' );
		$email = $request->get( 'email', '' );
		$bio = $request->get( 'bio', '' );
		$image = $request->get( 'image', null );
		$userId = $request->get('userId', '');
		$source = $request->get('source', '');
		$facebookId = $request->get( 'facebookId', null );
		$twitterId = $request->get( 'twitterId', null );
		
		$result['success'] = 0;
		$isNew = false;
		$save = true;
		$withAD = false;

		switch( $source ) {
			
			// TWITTER
			case 'twitter':
				$withAD = true;
				if( !is_null( $id ) ) {
					
					if( !is_null( $facebookId ) ) {
						$fbUser = $em->getRepository( 'ProtonRigbagBundle:User' )->findOneBy( array( 'facebook_id' => $facebookId ) );
					} else {
						$fbUser = null;
					}
					
					$user = $em->getRepository( 'ProtonRigbagBundle:User' )->findOneBy( array( 'twitter_id' => $id ) );
						
					if ($user) {
						if( !$fbUser && !$user->getFacebookId() && !is_null( $facebookId ) ) {
							$user->setFacebookId( $facebookId );
						}
					}
					else {
						if( $fbUser ) {
							$user = $fbUser;
						}
						else {
							$isNew = true;
							$user = new User();
						}
						
						if( $facebookId && !$user->getFacebookId() ) {
							$user->setFacebookId( $facebookId );
						}
						if( $id && !$user->getTwitterId() ) {
							$user->setTwitterId( $id );
						}
						if( !$user->getEmail() ) {
							$user->setEmail( $email );
						}
						if( !$user->getName() ) {
							$user->setName( $name );
						}
						if( !$user->getBio() ) {
							$user->setBio( $bio );
						}
					}
				}
			break;
			
			// FACEBOOK
			case 'facebook':
				$withAD = true;
				if( !is_null( $id ) ) {
					
					if( !is_null( $twitterId ) ) {
						$twUser = $em->getRepository( 'ProtonRigbagBundle:User' )->findOneBy( array( 'twitter_id' => $twitterId ) );
					} else {
						$twUser = null;
					}
					
					$user = $em->getRepository( 'ProtonRigbagBundle:User' )->findOneBy( array( 'facebook_id' => $id ) );
					
					if ($user) {
						if( !$twUser && !$user->getTwitterId() && !is_null( $twitterId ) ) {
							$user->setTwitterId( $twitterId );
						}
					}
					else {
						if( $twUser ) {
							$user = $twUser;
						} 
						else {
							$isNew = true;
							$user = new User();
						}
						if( $id && !$user->getFacebookId() ) {
							$user->setFacebookId( $id );
						}
						if( $twitterId && !$user->getTwitterId() ) {
							$user->setTwitterId( $twitterId );
						}
						if( !$user->getEmail() ) {
							$user->setEmail( $email );
						}
						if( !$user->getName() ) {
							$user->setName( $name );
						}
						if( !$user->getBio() ) {
							$user->setBio( $bio );
						}
					}
				}
			break;
			
			// DEFAULT
			default:
				$save = false;
				if (!is_null($userId)) {
				
					$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
				
					if ($user) {
						$result['success'] = 1;
						$result['user'] = $this->userProfile($user);
					}
				
				}
		}
		if( $user ) {
			
			if( $save ) {
				if( $isNew ) {
					$user->setLocation( '' );
					$user->setAccountType( 'free' );
					$user->setState( 'enabled' );
					$em->persist( $user );
				}
				$em->flush();
			}
			
			// SAVE PROFILE PICTURE
			if( !$user->getProfilePicture() && $image ) {
				
				$paths = $this->container->getParameter('paths');
				$sizes				= array( array( 36, 36 ), array( 60, 60 ), array( 100, 100 ), array( 80, 80 ), array( 40, 40 ), array( 160, 160 ), array( 50, 50 ) );
				
				$confAzure			= $this->container->getParameter( 'azure' );
				$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];
				
				$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );
				
				// UPLOAD NEW
				$fileTpl	= $paths['storage']['avatar'] . $user->getId() . '_' . time() . '-%size%.jpg';
				$user->setProfilePicture( $fileTpl );
				
				$blobOptions	= new CreateBlobOptions();
				$blobOptions->setContentType( 'image/jpeg' );
				
				$em->flush();
				
				$orgPath		= str_replace( '%size%', 'org', $fileTpl );
				
				$fileContentSrc	= file_get_contents( $image );
				$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'adaptiveResize' =>false), true );
				$image->setFormat( 'JPG' );
				$fileContent	= $image->getImageAsString();
				
				$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );
				
				foreach( $sizes as $size ) {
				
					$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'resizeUp' => true ), true );
					$image->adaptiveResize( $size[0], $size[1] );
				
					$fPath		= str_replace( '%size%', $size[0] . 'x' . $size[1], $fileTpl );
					$fPath		= str_replace( '%ext%', 'jpg', $fPath );
				
					$image->setFormat( 'JPG' );
				
					$blobProxy->createBlockBlob( $confAzure['storage']['container'], $fPath, $image->getImageAsString(), $blobOptions );
				}
			}
			
			$result['success'] = 1;
			$result['user'] = $this->userProfile($user, $withAD);
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	protected function userProfile($user, $withAD = false ) {

		$interestsObj = $user->getInterests();
		$circlesObj = $user->getCircles();
		$interests = array();
		$circles = array();
		$usrImage = '';

		if( $interestsObj ) {
			foreach ($interestsObj as $obj) {
				$interests[] = (string) $obj->getId();
			}
		}

		if( $circlesObj ) {
			foreach ($circlesObj as $obj) {
				$circles[] = array('id' => $obj->getId(), 'name' => $obj->getName(), 'description' => $obj->getDescription(),
						'image' => array('url' => $this->getHost() . $this->generateUrl('image_sport', array('sportId' => $obj->getInterestId(), 'width' => 60, 'height' => 60)), 'width' => 60, 'height' => 60));
			}
		}

		$mediaUrl = $this->container->getParameter('azure.storage.url');
		$usrImage = $mediaUrl . $user->getProfilePicture();
		
		$usrImage = str_replace( '%size%', '80x80', $usrImage );
		$accessData = array( 'fbToken' => '', 'twToken' => '', 'twSecret' => '' );
		if( $withAD ) {
			if( $user->getFacebookId() ) {
				$accessData['fbToken'] = $user->getFacebookToken();
				$accessData['fbId']	= $user->getFacebookId();
			}
			if( $user->getTwitterId() ) {
				$twdata = ( is_array( $user->getTwitterToken() ) ? $user->getTwitterToken() : unserialize( $user->getTwitterToken() ) );
				$accessData['twToken'] = $twdata['oauth_token'];
				$accessData['twSecret'] = $twdata['oauth_token_secret'];
				$accessData['twId'] = $user->getTwitterId();
			}
		}

		return array('id' => $user->getId(), 'name' => $user->getName(), 'email' => $user->getEmail(),
					'location' => array( 'name' => $user->getLocation(), 'countryCode' => $user->getLocationCountryCode(), 'formated' => $user->getLocationFormated(), 'lat' => $user->getLocationLat(), 'lng' => $user->getLocationLng() ),
					'bio' => $user->getBio(), 'phone' => $user->getPhone(), 'postCode' => $user->getPostCode(), 'logedFacebook' => ($user->getFacebookId() ? true : false),
					'ad' => $accessData,
				'logedTwitter' => ($user->getTwitterId() ? true : false), 'logedGoogle' => ($user->getGoogleId() ? true : false), 'interests' => $interests, 'accountType' => $user->getAccountType(), 'state' => $user->getState(),
				'paypal' => $user->getPaypalId(), 'image' => $usrImage, 'expiredAt' => ($user->getExpiredAt() ? $user->getExpiredAt()->format('d.m.Y') : null), 'accountState' => $user->getAccountState(), 'circles' => $circles);

	}

	// APP SETTINGS
	/****************
	 *
	 */
	public function appSettingsAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$result = array();

		$this->get('session')->set('init', 1);

		$dictionary = $em->getRepository('ProtonRigbagBundle:Dictionary')->findOneBy(array('code' => 'item_condition'));
		$conditions = $em->getRepository('ProtonRigbagBundle:DictionaryValue')->findForDictionary($dictionary);

		$result['conditionOptions'] = array();

		foreach ($conditions as $condition) {
			$result['conditionOptions'][] = array('id' => $condition->getId(), 'name' => $condition->getName());
		}

		$result['success'] = 1;
		$result['currencies'] = Transaction::getCurrencies();
		$result['remoteLog'] = 1;
		$result['sessionId'] = $this->get('session')->getId();

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// INTERESTS LIST
	/******************
	 *
	 */
	public function interestsListAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$interests = array();
		$objs = $em->getRepository('ProtonRigbagBundle:Interest')->findAll();

		foreach ($objs as $obj) {
			$interests[] = array('id' => $obj->getId(), 'name' => $obj->getName(), 'image' => 'http://beta.rigbag.com/image/sport-thumb/' . $obj->getId() . '/80/80/' );
		}

		$result['interests'] = $interests;
		$result['success'] = 1;

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);

	}

	// UPDATE PROFILE
	/******************
	 *
	 */
	public function updateProfileAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$userId = $request->get('userId', null);
		$name = $request->get('name', null);
		$email = $request->get('email', null);
		$location = $request->get('location', null);
		$locationCC = $request->get('locationCC', null);
		$locationFormated = $request->get('locationFormated', null);
		$locationLat = $request->get('locationLat', null);
		$locationLng = $request->get('locationLng', null);
		$bio = $request->get('bio', null);
		$phone = $request->get('phone', null);
		$paypal = $request->get( 'paypal', null );

		if (!is_null($userId) && !is_null($name) && !is_null($email) && !is_null($location) ) {

			$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);

			if( is_null( $phone ) ) {
				$phone = '';
			}
			if( is_null( $paypal ) ) {
				$paypal = '';
			}
			if( is_null( $bio ) ) {
				$bio = '';
			}

			if( !$user ) {
				$response = new JsonResponse( array( 'message' => 'User dosen\'t exist' ), 404 );
			} 
			else {
				
				$chUser = $em->getRepository('ProtonRigbagBundle:User')->findOneBy( array( 'email' => $email ) );
				
				if( !$chUser || $chUser->getId() == $user->getId() ) {
				
					$user->setName($name)->setEmail($email)->setLocation($location)->setPhone($phone)->setBio($bio)->setPaypalId( $paypal )
							->setLocationCountryCode( $locationCC )->setLocationFormated( $locationFormated )->setLocationLat( $locationLat )
							->setLocationLng( $locationLng );
		
					$em->flush();
					
					$result['success'] = 1;
					$result['user'] = $this->userProfile($user);
				}
				else {
					$result['success'] = 0;
					$result['message'] = 'This email is in use by another account.';
				}
				
				$response = new JsonResponse( $result, 200 );
			}

			
		} else {
			$response = new JsonResponse( array( 'message' => 'Unrecognized request' ), 400 );
		}

		$response->setCallback( $request->get( 'callback' ) );
		return $response;
	}

	// ASK CIRCLE (POST)
	/**********************
	 *
	 */
	public function askCircleAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$message = $request->get('message', null);
		$advertId = $request->get('advertId', null);
		$fromUserId = $request->get('fromUserId', null);
		$circleId = $request->get('circleId', null);

		if ($advertId) {
			$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);
		}
		$user = $em->getRepository('ProtonRigbagBundle:User')->find($fromUserId);
		$circle = $em->getRepository('ProtonRigbagBundle:Circle')->find($circleId);
		$qa = new QaPosition();

		$qa->setCircle($circle)->setBody($message)->setState('public')->setUser($user)->setAnswersNum(0);

		if ($advertId) {
			$qa->setAdvert($advert);
		}

		$em->persist($qa);
		$em->flush();

		$result = array('success' => 1);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// MESSAGE REPLY (POST)
	/***********************
	 *
	 */
	public function messageReplyAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$message = $em->getRepository('ProtonRigbagBundle:QaPosition')->find($request->get('replyOnId', null));
		$user = $em->getRepository('ProtonRigbagBundle:User')->find($request->get('fromUserId', null));

		$qa = new QaPosition();

		$qa->setToUser($message->getUser())->setBody($request->get('message', ''))->setAdvert($message->getAdvert())->setState('private')->setQuestion($message)->setUser($user)->setAnswersNum(0);

		$message->setAnswersNum($message->getAnswersNum() + 1);

		$em->persist($qa);
		$em->flush();

		$question = $message;
		$answers = $question->getAnswers();

		$result = array('success' => 1 );
		$result['messages'] = array();
		
		$result['question'] = array( 'id' => $question->getId(), 'advert' => $this->getAdvertSimple($question->getAdvert()), 'addedAgo' => $question->getAddedAgo(), 'answersNum' => 0, 'content' => stripslashes($question->getBody()), 'user' => $this->getUserSimple($question->getUser(), 60, 60));
		foreach ($answers as $answer) {
			$result['messages'][] = array('id' => $answer->getId(), 'addedAgo' => $answer->getAddedAgo(), 'answersNum' => 0, 'content' => stripslashes($answer->getBody()), 'user' => $this->getUserSimple($answer->getUser(), 60, 60));
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// PROFILE INFO (GET)
	/**********************
	 *
	 */
	public function profileInfoAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$userId = $request->get('userId', null);

		$userObj = $em->getRepository('ProtonRigbagBundle:User')->find($userId);

		$imgWidth = 160;
		$imgHeight = 160;

		$mediaUrl = $this->container->getParameter('azure.storage.url');

		$imageUrl = $mediaUrl . str_replace('%size%', $imgWidth . 'x' . $imgHeight, $userObj->getProfilePicture());

		if (!$this->msBlobFileExist($imageUrl)) {
			$this->msBlobImageResize($userObj->getProfilePicture(), array('w' => $imgWidth, 'h' => $imgHeight));
		}

		$user = array('id' => $userObj->getId(), 'name' => stripslashes($userObj->getName()), 'location' => stripslashes($userObj->getLocation()), 'bio' => stripslashes($userObj->getBio()),
				'image' => array('url' => $imageUrl, 'width' => $imgWidth, 'height' => $imgHeight));

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('success' => 1, 'user' => $user)), true);
	}

	// USER CIRCLES (GET)
	/**********************
	 *
	 */
	public function userCirclesAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$userId = $request->get('userId', null);

		$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
		$circlesObj = $user->getCircles();
		$circles = array();

		foreach ($circlesObj as $circleObj) {
			$circles[] = array('id' => $circleObj->getId(), 'name' => $circleObj->getName(), 'description' => $circleObj->getDescription(),
					'image' => array('url' => $this->getHost() . $this->generateUrl('image_sport', array('sportId' => $circleObj->getInterestId(), 'width' => 60, 'height' => 60)), 'width' => 60, 'height' => 60));
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('circles' => $circles)), true);
	}
	
	// ADVERT TAKE OFFER
	/********************
	*
	*/
	public function advertTakeSendAction( Request $request )
	{
		$em 		= $this->getDoctrine()->getManager();
		$message	= $request->get('message', null);
		$userId 	= $request->get('userId', null );
		$advertId 	= $request->get('advertId', null );
		$result 	= array( 'success' => 0 );
	
		if( $message && $userId && $advertId ) {
						
			$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
						
						
			$qa		= new QaPosition();
	
			$qa->setUser( $user )
					->setBody( $message )
					->setState( 'free_suggest' )
					->setAnswersNum( 0 )
					->setAdvert( $advert )
					->setToUser( $advert->getUser() );
						
			$em->persist( $qa );
			$em->flush();
						
			if( $qa->getId() ) {
	
				$result = array(
						'success' 		=> 1,
						'messageId'		=> $qa->getId()
				);
	
				$result2 = $this->send( 'freeOffer', array( 'advertId' => $advertId, 'qaId' => $result['messageId'] ) );
							
			}
		}
	
		$result['success'] = $result2['success'];
	
		$response = new JsonResponse( $result, 200 );
		$response->setCallback( $request->get( 'callback' ) );
	
		return $response;
	}
	
	// ADVERT SWAP OFFER
	/********************
	 * 
	 */
	public function advertSwapSendAction( Request $request )
	{
		$em 		= $this->getDoctrine()->getManager();
		$message	= $request->get('message', null);
		$userId 	= $request->get('userId', null );
		$advertId 	= $request->get('advertId', null );
		$result 	= array( 'success' => 0 );
		
		if( $message && $userId && $advertId ) {
			
			$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
			
			
			$qa		= new QaPosition();
		
			$qa->setUser( $user )
					->setBody( $message )
					->setState( 'swap_suggest' )
					->setAnswersNum( 0 )
					->setAdvert( $advert )
					->setToUser( $advert->getUser() );
					
			$em->persist( $qa );
			$em->flush();
			
			if( $qa->getId() ) {
				
				$result = array(
					'success' 		=> 1,
					'messageId'		=> $qa->getId()
				);
				
				$result2 = $this->send( 'swapOffer', array( 'advertId' => $advertId, 'qaId' => $result['messageId'] ) );
			
			}
		}
		
		$result['success'] = $result2['success'];
		
		$response = new JsonResponse( $result, 200 );
		$callback 	= $request->get( 'callback', null );
		
		if( $callback ) {
			$response->setCallback( $callback );
		}
		
		return $response;
	}

	// ADVERT MESSAGE (GET)
	/***********************
	 *
	 */
	public function advertMessageAction(Request $request) {

		$em 		= $this->getDoctrine()->getManager();
		$messageId 	= $request->get('messageId', null);
		$userId 	= $request->get('userId', null );
		$advertId 	= $request->get('advertId', null );
		$result 	= array();
		
		if( $messageId && $userId && $advertId ) {
		
			$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);
			$question = $em->getRepository('ProtonRigbagBundle:QaPosition')->find($messageId);
			
			if( $question->getUserId() == $userId || $question->getToUserId() == $userId ) {
				$answers = $question->getAnswers();
				if( $question->getToUserId() == $userId ) {
					$question->setReaded( 1 );
				}
				$messages = array();
	
				$result['question'] = array('id' => $question->getId(), 'addedAgo' => $question->getAddedAgo(), 'state' => $question->getState(), 'content' => stripslashes($question->getBody()), 'fromUser' => $this->getUserSimple($question->getUser(), 60, 60));
				
				foreach ($answers as $answer) {
					$messages[] = array('id' => $answer->getId(), 'addedAgo' => $answer->getAddedAgo(), 'state' => $answer->getState(), 'content' => stripslashes($answer->getBody()), 'fromUser' => $this->getUserSimple($answer->getUser(), 60, 60));
					if( $answer->getToUserId() ) {
						$answer->setReaded( 1 );
					}
				}
				$result['answers'] = $messages;
				
				$mediaUrl = $this->container->getParameter('azure.storage.url');
				$imagesObj = $advert->getImages();
				$thumbs = array();
				$thumbWidth = 296;
				$thumbHeight = 296;
				
				foreach ($imagesObj as $imageObj) {
				
					$imageUrl = $mediaUrl . str_replace('%size%', $thumbWidth . 'x' . $thumbHeight, $imageObj->getPath());
					if (!$this->msBlobFileExist($imageUrl)) {
						$this->msBlobImageResize($imageObj->getPath(), array('w' => $thumbWidth, 'h' => $thumbHeight));
					}
					$thumbs[] = array('url' => $imageUrl, 'width' => $thumbWidth, 'height' => $thumbHeight, 'id' => $imageObj->getId() );
				
					break;
				}
				
				$images = array('thumbs' => $thumbs);
				
				$advertData	= array(
						'id'			=> $advert->getId(),
						'images'		=> $images,
						'mode' 			=> $advert->getMode(),
						'user'			=> array( 'id' => $advert->getUserId() ),
						'description' 	=> stripslashes($advert->getDescription())
							
				);
				
				$result['advert']	= $advertData;
				
				$em->flush();
			}
			
		}

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	public function userSocialUpdateAction( Request $request ) {
		
		$em 		= $this->getDoctrine()->getManager();
		$userId 	= $request->get('userId', null );
		$socialData	= $request->get('socialData', null );
		$result		= array( 'success' => 0, 'errorMsg' => 'Unknown error' );
		
		$user = $em->getRepository('ProtonRigbagBundle:User')->find( $userId );
		
		if( $user && $socialData ) {
			switch( $socialData['source'] ) {
				case 'facebook':
					$user->setFacebookId( $socialData['id'] );
					$token = $socialData['token'];
					$user->setFacebookToken( $token );
					$em->flush();
					$result['success'] = 1;
					$result['errorMsg'] = '';
				break;
				case 'twitter':
					$user->setTwitterId( $socialData['id'] );
					$token = serialize( array( 'oauth_token' => $socialData['token'], 'oauth_token_secret' => $socialData['secret'], 'user_id' => $socialData['id'] ) );
					$user->setTwitterToken( $token );
					$em->flush();
					$result['success'] = 1;
					$result['errorMsg'] = '';
				break;
			}
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	public function userSocialAddAction( Request $request ) {
		
		$em 		= $this->getDoctrine()->getManager();
		$userId 	= $request->get('userId', null );
		$socialData	= $request->get('socialData', null );
		$network	= $request->get('network', null );
		$result		= array( 'success' => 0, 'errorMsg' => 'Unknown error' );
		
		$user = $em->getRepository('ProtonRigbagBundle:User')->find( $userId );
		
		if( $user && $socialData && $network ) {
			switch( $network ) {
				case 'twitter':
					$oUser = $em->getRepository('ProtonRigbagBundle:User')->findOneBy( array( 'twitter_id' => $socialData['id'] ) );
					
					if( $oUser ) {
						$result['errorMsg'] = 'User exists';
					} 
					else {
						if( !$user->getTwitterId() ) {
							$user->setTwitterId( $socialData['id'] );
							$token = serialize( array( 'oauth_token' => $socialData['token'], 'oauth_token_secret' => $socialData['secret'], 'user_id' => $socialData['id'], 'screen_name' => $socialData['name'] ) );
							$user->setTwitterToken( $token );
							$em->flush();
							
							$result['errorMsg']	= '';
							$result['success'] 	= 1;
						} 
						else {
							$result['errorMsg']	= 'This account is connected with Twitter';
						}
					}
				break;
				case 'facebook':
					$oUser = $em->getRepository('ProtonRigbagBundle:User')->findOneBy( array( 'facebook_id' => $socialData['id'] ) );
						
					if( $oUser ) {
						$result['errorMsg'] = 'User exists';
					}
					else {
						if( !$user->getFacebookId() ) {
							$user->setFacebookId( $socialData['id'] );
							$token = $socialData['token'];
							$user->setFacebookToken( $token );
							$em->flush();
						
							$result['errorMsg']	= '';
							$result['success'] 	= 1;
						} 
						else {
							$result['errorMsg']	= 'This account is connected with Facebook';
						}
					}
				break;
			}
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	public function userSocialDeleteAction( Request $request ) {
	
		$em 		= $this->getDoctrine()->getManager();
		$userId 	= $request->get('userId', null );
		$network	= $request->get('network', null );
		$result		= array( 'success' => 0 );
		
		$user = $em->getRepository('ProtonRigbagBundle:User')->find( $userId );
		
		if( $user && $network ) {
			switch( $network ) {
				case 'twitter':
					if( $user->getFacebookId() ) {
						$user->setTwitterId( null );
						$user->setTwitterToken( null );
						$em->flush();
		
						$result['success'] = 1;
					}
					break;
				case 'facebook':
					if( $user->getTwitterId() ) {
						$user->setFacebookId( null );
						$user->setFacebookToken( null );
						$em->flush();
							
						$result['success'] = 1;
					}
					break;
			}
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	// ADVERT MESSAGES DELETE (GET)
	/************************
	 *
	*/
	public function advertMessageDeleteAction(Request $request) {
	
		$em 		= $this->getDoctrine()->getManager();
		$userId 	= $request->get('userId', null);
		$messageId	= $request->get('messageId', null);
		$result		= array();
		
		if( $userId && $messageId ) {
			
			$message = $em->getRepository('ProtonRigbagBundle:QaPosition')->find($messageId);
			
			if( $message->getUserId() == $userId || $message->getAdvert()->getUserId() == $userId ) {
				$em->remove( $message );
				$em->flush();
			
				if( $message->getParentId() ) {
					$parent = $message->getParent();
					$parent->setAnswersNum( count( $parent->getAnswers() ) );
					$em->flush();
				}
			}
		}
		
		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}
	
	public function advertMessageRejectAction( Request $request )
	{
		$em = $this->getDoctrine()->getManager();
		$advertId = $request->get('advertId', null);
		$userId = $request->get('userId', null);
		$messageId = $request->get('messageId', null);
		$result = array( 'success' => 0 );
	
		if( $advertId && $userId && $messageId )
		{
			$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
			$message = $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $messageId );
				
			if(
					$user && $advert && $message && $message->getAdvertId() == $advert->getId() &&
					$advert->getUserId() == $user->getId() && $advert->getState() == 'enabled' &&
					( $message->getState() == 'swap_suggest' || $message->getState() == 'free_suggest' )
			) {
				switch( $message->getState() )
				{
					case 'swap_suggest':
						
						$this->systemMessage( 'swapRejected', array( 'advert' => $message->getAdvert(), 'toUser' => $message->getUser() ) );
						$message->setState( 'swap_rejected' );
						$advert = $message->getAdvert();
						$user = $message->getUser();
	
						$transaction		= new Transaction();
	
						$transaction->setFromUser( $user )
							->setFromUserEmail( $user->getEmail() )
							->setFromUserName( $user->getName() )
							->setToUser( $advert->getUser() )
							->setToUserEmail( $advert->getUser()->getEmail() )
							->setToUserName( $advert->getUser()->getName() )
							->setAdvert( $advert )
							->setAmount( null )
							->setCurrency( null )
							->setDescription( 'Swap items - advert no.' . $advert->getId() )
							->setType( 'swap' )
							->setMethod( '-' )
							->setState( 'rejected' );
	
						$em->persist( $transaction );
	
						$em->flush();
						$this->send( 'swapRejected', array( 'swapId' => $message->getId() ) );
						break;
					case 'free_suggest':
						$this->systemMessage( 'freeRejected', array( 'advert' => $message->getAdvert(), 'toUser' => $message->getUser() ) );
						$message->setState( 'free_rejected' );
						$advert = $message->getAdvert();
						$user = $message->getUser();
	
						$transaction		= new Transaction();
	
						$transaction->setFromUser( $user )
								->setFromUserEmail( $user->getEmail() )
								->setFromUserName( $user->getName() )
								->setToUser( $advert->getUser() )
								->setToUserEmail( $advert->getUser()->getEmail() )
								->setToUserName( $advert->getUser()->getName() )
								->setAdvert( $advert )
								->setAmount( null )
								->setCurrency( null )
								->setDescription( 'Take item - advert no.' . $advert->getId() )
								->setType( 'freebie' )
								->setMethod( '-' )
								->setState( 'rejected' );
	
						$em->persist( $transaction );
	
						$em->flush();
						$this->send( 'freeRejected', array( 'freeId' => $message->getId() ) );
						break;
				}
				$result['success'] = 1;
			}
		}
	
		$response = new JsonResponse( $result, 200 );
		$response->setCallback( $request->get( 'callback' ) );
	
		return $response;
	}

	public function advertMessageAcceptAction( Request $request )
	{
		$em = $this->getDoctrine()->getManager();
		$advertId = $request->get('advertId', null);
		$userId = $request->get('userId', null);
		$messageId = $request->get('messageId', null);
		$result = array( 'success' => 0 );
		
		if( $advertId && $userId && $messageId )
		{
			$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
			$message = $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $messageId );
			
			if( 
				$user && $advert && $message && $message->getAdvertId() == $advert->getId() && 
				$advert->getUserId() == $user->getId() && $advert->getState() == 'enabled' && 
				( $message->getState() == 'swap_suggest' || $message->getState() == 'free_suggest' ) && 
				!$em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $advert->getId() )
			) {
				switch( $message->getState() )
				{
					case 'swap_suggest':
						$this->systemMessage( 'swapAccepted', array( 'advert' => $message->getAdvert(), 'toUser' => $message->getUser() ) );
						$message->setState( 'swap_accepted' );
						$advert = $message->getAdvert();
						$advert->setState( 'closed' );
						$user = $message->getUser();
						
						$transaction		= new Transaction();
						
						$transaction->setFromUser( $user )
								->setFromUserEmail( $user->getEmail() )
								->setFromUserName( $user->getName() )
								->setToUser( $advert->getUser() )
								->setToUserEmail( $advert->getUser()->getEmail() )
								->setToUserName( $advert->getUser()->getName() )
								->setAdvert( $advert )
								->setAmount( null )
								->setCurrency( null )
								->setDescription( 'Swap items - advert no.' . $advert->getId() )
								->setType( 'swap' )
								->setMethod( '-' )
								->setState( 'completed' );
						
						$em->persist( $transaction );
						
						$em->flush();
						$this->send( 'swapAccepted', array( 'swapId' => $message->getId() ) );
					break;
					case 'free_suggest':
						$this->systemMessage( 'freeAccepted', array( 'advert' => $message->getAdvert(), 'toUser' => $message->getUser() ) );
						$message->setState( 'free_accepted' );
						$advert = $message->getAdvert();
						$advert->setState( 'closed' );
						$user = $message->getUser();
						
						$transaction		= new Transaction();
						
						$transaction->setFromUser( $user )
								->setFromUserEmail( $user->getEmail() )
								->setFromUserName( $user->getName() )
								->setToUser( $advert->getUser() )
								->setToUserEmail( $advert->getUser()->getEmail() )
								->setToUserName( $advert->getUser()->getName() )
								->setAdvert( $advert )
								->setAmount( null )
								->setCurrency( null )
								->setDescription( 'Take item - advert no.' . $advert->getId() )
								->setType( 'freebie' )
								->setMethod( '-' )
								->setState( 'completed' );
						
						$em->persist( $transaction );
						
						$em->flush();
						$this->send( 'freeAccepted', array( 'freeId' => $message->getId() ) );
					break;
				}
				$result['success'] = 1;
			}
		}
		
		$response = new JsonResponse( $result, 200 );
		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}
	
	// ADVERT MESSAGES (GET)
	/************************
	 *
	 */
	public function advertMessagesAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$advertId = $request->get('advertId', null);
		$userId = $request->get('userId', null);

		$messages = array();

		$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);

		if ($advert->getUserId() == $userId) {
			$ownAdvert = true;
		} else {
			$ownAdvert = false;
		}

		$questions = $em->getRepository('ProtonRigbagBundle:QaPosition')->findForAdvert($advert->getId(), array('ownAdvert' => $ownAdvert, 'userId' => $userId));

		foreach ($questions as $question) {
			$new = 0;
			$all = 0;
			$answers = $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findBy( array( 'parent_id' => $question->getId(), 'advert_id' => $advertId, 'to_user_id' => $userId ) );
			if( $question->getToUserId() == $userId && !$question->getReaded() ) {
				$new++;
			} 
			foreach( $answers as $answer ) {
				$all++;
				if( !$answer->getReaded() ) {
					$new++;
				}
			}
			$messages[] = array('id' => $question->getId(), 'new' => $new, 'all' => $all, 'addedAgo' => $question->getAddedAgo(), 'state' => $question->getState(), 'content' => stripslashes($question->getBody()), 'fromUser' => $this->getUserSimple($question->getUser(), 60, 60));
		}
		
		$mediaUrl = $this->container->getParameter('azure.storage.url');
		$imagesObj = $advert->getImages();
		$thumbs = array();
		$thumbWidth = 296;
		$thumbHeight = 296;
		
		foreach ($imagesObj as $imageObj) {
		
			$imageUrl = $mediaUrl . str_replace('%size%', $thumbWidth . 'x' . $thumbHeight, $imageObj->getPath());
			if (!$this->msBlobFileExist($imageUrl)) {
				$this->msBlobImageResize($imageObj->getPath(), array('w' => $thumbWidth, 'h' => $thumbHeight));
			}
			$thumbs[] = array('url' => $imageUrl, 'width' => $thumbWidth, 'height' => $thumbHeight, 'id' => $imageObj->getId() );
		
			break;
		}
		
		$images = array('thumbs' => $thumbs);
		
		$advertData	= array(
			'id'			=> $advert->getId(),
			'images'		=> $images,
			'mode' 			=> $advert->getMode(), 
			'user'			=> array( 'id' => $advert->getUserId() ),
			'description' 	=> stripslashes($advert->getDescription())
					
		);

		$result['messages'] = $messages;
		$result['advert'] = $advertData;

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	// ADVERT VIEW (GET)
	/********************
	 *
	 */
	public function advertViewAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$advertId = $request->get('advertId', null);
		$userId = $request->get('userId', null);
		$simple = $request->get( 'simple', 0 );

		$advert = $em->getRepository('ProtonRigbagBundle:Advert')->find($advertId);
		$userObj = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
		
		if ($advert && $userObj) {

			$userOwnerObj = $em->getRepository('ProtonRigbagBundle:User')->find($advert->getUserId());

			if ($advert->getUserId() == $userId) {
				$ownAdvert = true;
			} else {
				$ownAdvert = false;
			}

			$userImgWidth = 60;
			$userImgHeight = 60;

			$messages = array();
			$user = $this->getUserSimple($userOwnerObj, $userImgWidth, $userImgHeight);

			$price = array('amount' => $advert->getPrice(), 'currency' => ($advert->getCurrency() == 'eur' ? '&euro;' :  strtoupper( $advert->getCurrency() ) ), 'currencyCode' => $advert->getCurrency() );

			$images = array();
			$circles = array();
			

			$circlesObj = $advert->getCircles();

			foreach ($circlesObj as $circleObj) {
				$circles[] = array('id' => $circleObj->getId(), 'name' => stripslashes($circleObj->getName()));
			}

			$imagesObj = $advert->getImages();
			$thumbs = array();
			$full = array();
			$thumbWidth = 296;
			$thumbHeight = 296;
			$fullWidth = 640;
			$fullHeight = 960;

			$mediaUrl = $this->container->getParameter('azure.storage.url');

			foreach ($imagesObj as $imageObj) {

				$imageUrl = $mediaUrl . str_replace('%size%', $thumbWidth . 'x' . $thumbHeight, $imageObj->getPath());

				if (!$this->msBlobFileExist($imageUrl)) {
					$this->msBlobImageResize($imageObj->getPath(), array('w' => $thumbWidth, 'h' => $thumbHeight));
				}

				$thumbs[] = array('url' => $imageUrl, 'width' => $thumbWidth, 'height' => $thumbHeight, 'id' => $imageObj->getId() );

				$imageUrl = $mediaUrl . str_replace('%size%', $fullWidth . 'x' . $fullHeight, $imageObj->getPath());

				if( $imageUrl ) {
					$imageUrl = str_replace( 'https://', 'http://', $imageUrl );
				}
				
				if (!$this->msBlobFileExist($imageUrl)) {
					$this->msBlobImageResize($imageObj->getPath(), array('w' => $fullWidth, 'h' => $fullHeight));
				}

				$full[] = array('url' => $imageUrl, 'width' => $fullWidth, 'height' => $fullHeight, 'id' => $imageObj->getId() );

			}

			$images = array('full' => $full, 'thumbs' => $thumbs);

			$all = 0;
			$new = 0;
			$total = 0;
			
			$tmp = $em->getRepository('ProtonRigbagBundle:QaPosition')->findForAdvert( $advert->getId(), array( 'ownAdvert' => $ownAdvert, 'userId' => $userObj->getId() ) );
			
			foreach( $tmp as $t ) {
				$tmp2 = $t->getAnswers();
				$total++;
				if( $t->getToUserId() == $userId ) {
					$all++;
					if( !$t->getReaded() ) {
						$new++;
					}
				}
				foreach( $tmp2 as $t2 ) {
					$total++;
					if( $t2->getToUserId() == $userId ) {
						$all++;
						if( !$t2->getReaded() ) {
							$new++;
						}
					}
				}
			}
			
			$messages = array( 
					'all' => $all, 
					'new' => $new,
					'total' => $total
				);

			$lockTransaction = false;
			
			if( $advert->getMode() == 'swap' || $advert->getMode() == 'freebie' ) {
				$lockTransaction = $em->getRepository('ProtonRigbagBundle:QaPosition')->hasAdvertAcceptedSwapOrFree( $advert->getId() );
			}

			$result = array('id' => $advert->getId(), 'conditionId' => $advert->getConditionId(), 'mode' => $advert->getMode(), 'description' => stripslashes($advert->getDescription()), 'location' => stripslashes($advert->getLocationDisplay()), 'addedAgo' => $advert->getAddedAgo(), 'user' => $user, 'images' => $images, 'price' => $price,
							'url' => $this->getHost() . $this->generateUrl( 'advert_short_url', array( 'hash' => $advert->getHash() ) ),
							'lockTransaction' => $lockTransaction,
							'circles' => $circles, 'messages' => $messages, 'locationName' => stripslashes( $advert->getLocation() ), 'locationFormated' => stripslashes( $advert->getLocationFormated() ),
							'locationLat' => $advert->getLocationLat(), 'locationLng' => $advert->getLocationLng(), 'paypal' => $advert->getPaypalId(), 'swapFor' => $advert->getSwapFor() );

			if( !$simple ) {
				$result = array( $result );
			}
			
			$response	= new JsonResponse( $result, 200 );
		}
		// Error
 		else {
 			if( !$advert ) {
 				$response	= new JsonResponse( array( 'message' => 'Advert doesn\'t exist' ), 404 );
 			}
 			else if( !$userObj ) {
 				$response	= new JsonResponse( array( 'message' => 'User doesn\'t exist' ), 404 );
 			}
 			else {
 				$response	= new JsonResponse( array( 'message' => 'Unrecognized request' ), 400 );
 			}
		}

		$response->setCallback( $request->get( 'callback' ) );
		
		return $response;
	}

	// ADVERTS LIST (GET)
	/*********************
	 * byMode		: userId + mode
	 * byUser		: userId
	 * byCircle		: circleId
	 *
	 * fromId		: use if you want more results
	 */
	public function advertsListAction(Request $request) {

		$userId = $request->get('userId', null);
		$mode = $request->get('mode', null);
		$circleId = $request->get('circleId', null);
		$offset = $request->get('offset', 0);
		$em = $this->getDoctrine()->getManager();
		$fromId = $request->get('fromId', null );

		if ($fromId || $offset) {
			$limit = self::$LIMIT_ADVERTS_NEXT;
		} else {
			$limit = self::$LIMIT_ADVERTS_FIRST;
		}

		// by Type
		if (!is_null($userId) && !is_null($mode)) {

// 			$user = $em->getRepository('ProtonRigbagBundle:User')->find($userId);
// 			$circles = $user->getCircles();
// 			$circlesIds = array();
// 			foreach ($circles as $circle) {
// 				$circlesIds[] = $circle->getId();
// 			}

			$advertsResult = $em->getRepository('ProtonRigbagBundle:Advert')->search('', array('mode' => $mode, 'offset' => $offset, 'limit' => $limit));

		}
		// by User
 		elseif (!is_null($userId)) {
 			$isOwn = $request->get( 'isown', 0 );
 			if( $isOwn ) {
 				$states	= array(
 					'enabled', 'disabled', 'closed', 'waiting_for_payment', 'during_deal', 'sold'	
 				);
 			} else {
 				$states = array(
 					'enabled'	
 				);
 			}
			$advertsResult = $em->getRepository('ProtonRigbagBundle:Advert')->search('', array('userId' => $userId, 'offset' => $offset, 'limit' => $limit, 'states' => $states));
		}
		// by Circle
		 elseif (!is_null($circleId)) {
			$searchParams = array('circles' => array($circleId), 'offset' => $offset, 'limit' => $limit);
			$advertsResult = $em->getRepository('ProtonRigbagBundle:Advert')->search('', $searchParams);
		}
		// Error
		 else {
			return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => array('message' => 'Unrecognized request')), true, 400);
		}

		$imgWidth = 160;
		$imgHeight = 160;
		$adverts = array();

		$mediaUrl = $this->container->getParameter('azure.storage.url');

		foreach ($advertsResult as $advertObj) {

			$mainImage = $advertObj->getMainImage();

			if ($mainImage) {
				$imageUrl = $mediaUrl . str_replace('%size%', $imgWidth . 'x' . $imgHeight, $mainImage->getPath());

				if (!$this->msBlobFileExist($imageUrl)) {
					$this->msBlobImageResize($mainImage->getPath(), array('w' => $imgWidth, 'h' => $imgHeight));
				}
			} else {
				$imageUrl = null;
			}
			
			if( $imageUrl ) {
				$imageUrl = str_replace( 'https://', 'http://', $imageUrl );
			}

			$advert = array('id' => $advertObj->getId(), 'state' => $advertObj->getState(), 'mode' => $advertObj->getMode(), 'addedAgo' => $advertObj->getAddedAgo(), 'description' => stripslashes($advertObj->getDescription()),
					'price' => array('amount' => $advertObj->getPrice(), 'currency' => ($advertObj->getCurrency() == 'eur' ? '&euro;' : strtoupper( $advertObj->getCurrency() ))),
					'image' => array('url' => $imageUrl, 'width' => $imgWidth, 'height' => $imgHeight), 'userId' => $advertObj->getUserId() );

			$adverts[] = $advert;
		}

		$result = array('adverts' => $adverts);

		return $this->jsonResponse(array('callback' => $request->get('callback'), 'result' => $result), true);
	}

	protected function getUserSimple($userObj, $imgWidth, $imgHeight) {

		$mediaUrl = $this->container->getParameter('azure.storage.url');

		$imageUrl = $mediaUrl . str_replace('%size%', $imgWidth . 'x' . $imgHeight, $userObj->getProfilePicture());

		return array('id' => $userObj->getId(), 'name' => stripslashes($userObj->getName()), 'location' => stripslashes($userObj->getLocation()), 'image' => array('url' => $imageUrl, 'width' => $imgWidth, 'height' => $imgHeight));
	}

	protected function getAdvertSimple($advertObj) {
		if ($advertObj && $advertObj->getId()) {
			return array('id' => $advertObj->getId(), 'description' => stripslashes($advertObj->getDescription()), 'location' => $advertObj->getLocation(), 'price' => array('amount' => $advertObj->getPrice(), 'currency' => $advertObj->getCurrency()), 'mode' => $advertObj->getMode());
		} else {
			return array();
		}
	}

	// WELCOME SCREEN
	public function indexAction() {

		echo 'RigBag - Powering Action Sports<br/>';
		echo 'API version 0.1<br/>';
		echo 'Proton Labs Ltd';
		exit();
	}

}
