<?php

namespace Proton\RigbagBundle\Controller;

use Proton\RigbagBundle\Entity\Transaction;

use Symfony\Component\HttpFoundation\JsonResponse;

use Proton\RigbagBundle\Repository\QaPositionRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\Location;
use Proton\RigbagBundle\Entity\Circle;
use Proton\RigbagBundle\Entity\QaPosition;
use Proton\RigbagBundle\Entity\User;

class QaController extends \ProtonLabs_Controller
{


	public function shortUrlAction( $hash ) {

		$em 	= $this->getDoctrine()->getManager();
		$qa		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( \Proton\RigbagBundle\Entity\QaPosition::decodeHash( $hash ) );

		while( $qa->getParentId() ) {
			$qa		= $qa->getParent();
		}

		if( !$qa ) {
			return $this->redirect( $this->generateUrl( 'start', array( ) ) );
		}

		$this->get('session')->set( 'metaTags', array( 'type' => 'qa', 'id' => $qa->getId() ) );

		return $this->redirect( $this->generateUrl( 'start' ) . '#/qa/view/' . $qa->getId() . '/' );

	}

	public function searchAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$em 	= $this->getDoctrine()->getManager();

			$result		= array( 'content' => '' );

			$interestId	= $request->get( 'category', 0 );
			$query		= $request->get( 'query', '' );
			$type		= $request->get( 'type', 'main' );
			$engine 	= $this->container->get('templating');

				$circlesIds	= array();
				$searchParams	= array();
				switch( $type ) {
					case 'circles':
						$circlesIds[]	= $request->get( 'circle', 0 );
					break;
					default:
						if( $this->isLoged() ) {
							$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
							$tmp		= $user->getCircles();


							foreach( $tmp as $t ) {

								if( $interestId && $t->getInterestId() != $interestId ) {
									continue;
								}
								$circlesIds[]	= $t->getId();
							}
							$searchParams	= array( 'circles' => $circlesIds );
						}
				}


				$positions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->search( $query, $searchParams );

				switch( $type ) {
					case 'circles':
						$result['content']			= $engine->render( 'ProtonRigbagBundle:Circle:qa-content.html.twig', array( 'questions' => $positions ) );
					break;
					default:
						$result['content']			= $engine->render( 'ProtonRigbagBundle:Qa:questions-list.html.twig', array( 'questions' => $positions ) );
				}


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}


	public function deleteAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();


			$result		= array();

			$id			=(int)$request->get( 'id' );

			$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $id );


			if( $question->getUserId() == $this->getUserId() || $question->getAdvert()->getUserId() == $this->getUserId() ) {
				if( $question->getParentId() ) {
					$parent	= $question->getQuestion();
					$parent->removeAnswer( $question );
				}
				$em->remove( $question );
				$em->flush();

				if( isset($parent) ) {
					$parent->setAnswersNum( count( $parent->getAnswers() ) );
					$em->flush();
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function freeRejectAction( Request $request )
	{
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
			$freeId		= $request->get( 'freeId', 0 );

			if( $freeId )
			{
				$free		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $freeId );

				if( $free->getAdvert()->getUserId() == $this->getUserId() && $free->getState() == 'free_suggest' && $free->getAdvert()->getState() == 'enabled' )
				{
					$this->systemMessage( 'freeRejected', array( 'advert' => $free->getAdvert(), 'toUser' => $free->getUser() ) );

					$engine 	= $this->container->get('templating');
					$free->setState( 'free_rejected' );
					
					$transaction		= new Transaction();
					$user				= $free->getUser();
					$advert				= $free->getAdvert();
						
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

					$this->send( 'freeRejected', array( 'freeId' => $free->getId() ) );
					
					$user					= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $free->getAdvertId(), array( 'ownAdvert' => true, 'userId' => $this->getUserId() ) );
					$hasAccepted			= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $free->getAdvertId() );

					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-advert-list.html.twig', array( 'advert' => $free->getAdvert(), 'user' => $user, 'questions' => $questions, 'hasAccepted' => $hasAccepted ) );
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function swapRejectAction( Request $request )
	{
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
			$swapId		= $request->get( 'swapId', 0 );

			if( $swapId )
			{
				$swap		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $swapId );

				if( $swap->getAdvert()->getUserId() == $this->getUserId() && $swap->getState() == 'swap_suggest' && $swap->getAdvert()->getState() == 'enabled' )
				{
					$this->systemMessage( 'swapRejected', array( 'advert' => $swap->getAdvert(), 'toUser' => $swap->getUser() ) );

					$engine 	= $this->container->get('templating');
					$swap->setState( 'swap_rejected' );
					
					$transaction		= new Transaction();
					$user				= $swap->getUser();
					$advert				= $swap->getAdvert();
					
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

					$this->send( 'swapRejected', array( 'swapId' => $swap->getId() ) );
					
					$user					= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $swap->getAdvertId(), array( 'ownAdvert' => true, 'userId' => $this->getUserId() ) );
					$hasAccepted			= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $swap->getAdvertId() );

					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-advert-list.html.twig', array( 'advert' => $swap->getAdvert(), 'user' => $user, 'questions' => $questions, 'hasAccepted' => $hasAccepted ) );
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function freeAcceptAction( Request $request )
	{
			
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
			$freeId		= $request->get( 'freeId', 0 );

			if( $freeId )
			{
				$free		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $freeId );

				if( $free->getAdvert()->getUserId() == $this->getUserId() && $free->getState() == 'free_suggest' && $free->getAdvert()->getState() == 'enabled' )
				{
					$this->systemMessage( 'freeAccepted', array( 'advert' => $free->getAdvert(), 'toUser' => $free->getUser() ) );

					$engine 	= $this->container->get('templating');
					$free->setState( 'free_accepted' );

					$transaction		= new Transaction();
					$user				= $free->getUser();
					$advert				= $free->getAdvert();
					
					$advert->setState( 'closed' );
					
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
					
					$this->send( 'freeAccepted', array( 'freeId' => $free->getId() ) );

					$user					= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $free->getAdvertId(), array( 'ownAdvert' => true, 'userId' => $this->getUserId() ) );
					$hasAccepted			= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $free->getAdvertId() );

					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-advert-list.html.twig', array( 'advert' => $free->getAdvert(), 'user' => $user, 'questions' => $questions, 'hasAccepted' => $hasAccepted ) );
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function swapAcceptAction( Request $request )
	{
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
			$swapId		= $request->get( 'swapId', 0 );

			if( $swapId )
			{
				$swap		= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $swapId );

				if( $swap->getAdvert()->getUserId() == $this->getUserId() && $swap->getState() == 'swap_suggest' && $swap->getAdvert()->getState() == 'enabled' )
				{
					$this->systemMessage( 'swapAccepted', array( 'advert' => $swap->getAdvert(), 'toUser' => $swap->getUser() ) );

					$engine 	= $this->container->get('templating');
					$swap->setState( 'swap_accepted' );
					
					$transaction		= new Transaction();
					$user				= $swap->getUser();
					$advert				= $swap->getAdvert();
					$advert->setState( 'closed' );
						
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
					
					$this->send( 'swapAccepted', array( 'swapId' => $swap->getId() ) );

					$user					= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $swap->getAdvertId(), array( 'ownAdvert' => true, 'userId' => $this->getUserId() ) );
					$hasAccepted			= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $swap->getAdvertId() );

					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-advert-list.html.twig', array( 'advert' => $swap->getAdvert(), 'user' => $user, 'questions' => $questions, 'hasAccepted' => $hasAccepted ) );
				}
			}

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

			$response	= new Response();
			$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );
			$result		= array();
			$engine 	= $this->container->get('templating');

			$circleId	=  $request->get( 'circle', 0 );
			if( !$circleId ) {
				$circleId	= $request->get( 'circleId', 0 );
			}
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			if( $circleId ) {
				$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			}

			$extraType	= $request->get( 'extraType', '' );
			$extraData	= $request->get( 'extraData', '' );


			$qa		= new QaPosition();

			$qa->setUser( $user )
				->setBody( $request->get( 'question' ) )
				->setState( 'public' )
				->setAnswersNum( 0 );

			if( isset( $circle ) && $circle ) {
				$qa->setCircle( $circle );
			}

			switch( $extraType ) {
				case 'advertQuestion':
					$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $extraData );
					$qa->setAdvert( $advert );
				break;
				case 'advertSwap':
					$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $extraData );
					$qa->setAdvert( $advert );
					$qa->setState( 'swap_suggest' );
					$qa->setToUser( $advert->getUser() );
				break;
				case 'advertFree':
					$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $extraData );
					$qa->setAdvert( $advert );
					$qa->setState( 'free_suggest' );
					$qa->setToUser( $advert->getUser() );
					break;
				case 'advertPrivate':
					$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $extraData );
					$qa->setAdvert( $advert );
					$qa->setState( 'private' );
					$qa->setToUser( $advert->getUser() );
				break;
			}

			$em->persist( $qa );

			$em->flush();

			$em 	= $this->getDoctrine()->getManager();

			switch( $extraType ) {
				case 'advertFree':
				case 'advertSwap':
				case 'advertPrivate':
					$ownAdvert	= false;
					if( $advert->getUserId() == $this->getUserId() ) {
						$ownAdvert	= true;
					}

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForAdvert( $advert->getId(), array( 'ownAdvert' => $ownAdvert, 'userId' => $this->getUserId() ) );
					$hasAccepted			= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->hasAdvertAcceptedSwapOrFree( $advert->getId() );

					$result['qaId'] 		= $qa->getId();
					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-advert-list.html.twig', array( 'user' => $user, 'advert' => $advert, 'questions' => $questions, 'hasAccepted' => $hasAccepted ) );
				break;
				default:
					if( !isset( $circleId ) ) {
						$circleId	= null;
					}
					$circleId				= $request->get( 'circleId', $circleId );

					$questions				= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findForUser( $this->getUserId(), $circleId );
					$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:questions-list.html.twig', array( 'questions' => $questions ) );
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function refreshAction( Request $request ) {
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

			$engine 	= $this->container->get('templating');

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $request->get( 'qid' ) );

			$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:answers-list.html.twig', array( 'answers' => $question->getAnswers() ) );

			return new JsonResponse( $result );
		}

		return $this->blackholeResponse();

	}

	public function answerAction( Request $request ) {
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();

			$result		= array();

			$engine 	= $this->container->get('templating');

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $request->get( 'qid' ) );
			$extraType	= $request->get( 'extraType', '' );
			$extraData	= $request->get( 'extraData', '' );

			$hasAccess	= false;

			switch( $extraType ) {
				case 'advertPrivate':
					$advert	= $question->getAdvert();
					if( ( $question->getState() == 'private' || $question->getState() == 'swap_accepted' || $question->getState() == 'free_accepted' || $question->getState() == 'swap_suggest' || $question->getState() == 'free_suggest' ) && ( $advert->getUserId() == $this->getUserId() || $question->getUserId() == $this->getUserId() ) )  {
						$hasAccess	= true;
					}
				break;
				default:
					if( $question->getState() == 'public' )  {
						$hasAccess	= true;
					}
			}


			if( $hasAccess ) {

				$question->setAnswersNum( $question->getAnswersNum() + 1 );

				$qa		= new QaPosition();

				$qa->setUser( $user )
						->setBody( $request->get( 'answer' ) )
						->setState( 'public' )
						->setQuestion( $question );

				if( $question->getAdvert() ) {
					$qa->setAdvert( $question->getAdvert() )
						->setToUser( ( $user->getId() == $question->getUser()->getId() ? $question->getAdvert()->getUser() : $user ) );
				}

				if( $question->getCircle() ) {
					$qa->setCircle( $question->getCircle() );
				}

				switch( $extraType ) {
					case 'advertPrivate':
						$qa->setState( 'private' );
					break;

				}

				$em->persist( $qa );

				$em->flush();

				$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $request->get( 'qid' ) );

				switch( $extraType ) {
					case 'advertPrivate':
						$result['id']			= $qa->getId();
					break;
					default:
						$result['content']		= $engine->render( 'ProtonRigbagBundle:Qa:answers-list.html.twig', array( 'answers' => $question->getAnswers(), 'user' => $user ) );
				}
			}

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}


	public function viewAction( $qaId, Request $request ) {
		
		$this->setupLocale($request);

		if( $request->isXmlHttpRequest() )
		{
			$em 	= $this->getDoctrine()->getManager();

			$engine 	= $this->container->get('templating');

			$paths		= $this->container->getParameter( 'paths' );
			if( $this->isLoged() ) {
				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			} else {
				$user		= new user();
			}
			$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $qaId );

			if( $this->getUserId() && $question->getId() ) {
				$messages	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->findBy( array( 'to_user_id' => $this->getUserId(), 'parent_id' => $question->getId() ) );
				foreach( $messages as $message ) {
					$message->setReaded( 1 );
				}
			}

			$em->flush();

			if( $question->getState() == 'public' || $question->getUserId() == $this->getUserId() ) {

				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Qa:view-header-top.html.twig', array() ),
								'bottom'	=> '',
						),
						'bodyClass'	=> 'q-and-a-single',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Qa:view-content.html.twig', array( 'question' => $question, 'user' => $user ) )
				);

			}
			else {
				$result		= array();
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}


		return $this->blackholeResponse();

	}

	public function loadMoreAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{

			$listParams		= $this->get( 'session' )->get( 'listParams', null );
			$em 	= $this->getDoctrine()->getManager();

			$engine 	= $this->container->get('templating');

			if( $this->isLoged() ) {
				$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
				$circlesIds		= $listParams['circles'];
				$searchParams 	= array( 'circles' => $circlesIds );
			} else {
				$user			= new User();
				$searchParams 	= array();
			}

			$searchParams['limit']	= $listParams['pageSize'];
			$searchParams['offset']	= $listParams['offset'];

			$listParams['offset']	= $listParams['pageSize'] + $listParams['offset'];

			$this->get( 'session' )->set( 'listParams', $listParams );

			$positions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->search( '', $searchParams );


			if( count( $positions ) == $searchParams['limit'] ) {
				$full	= 1;
			} else {
				$full	= 0;
			}

			$result['content']			= $engine->render( 'ProtonRigbagBundle:Qa:load-more.html.twig', array( 'questions' => $positions, 'user' => $user ) );
			$result['full']				= $full;
			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function indexAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$listParams	= array(
							'offset'	=> 0,
							'pageSize'	=> 5
					);

			$em 	= $this->getDoctrine()->getManager();

			$response	= new Response();
			$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );

			$engine 	= $this->container->get('templating');

			$paths		= $this->container->getParameter( 'paths' );
			if( $this->isLoged() ) {
				$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
				$circlesIds	= array();
				$circles	= $em->getRepository( 'ProtonRigbagBundle:Circle' )->findForUser( $this->getUserId() );

				foreach( $circles as $c ) {
					$circlesIds[]	= $c->getId();
				}

				$searchParams = array( 'circles' => $circlesIds );
			} else {
				$user		= new User();
				$circles	= $em->getRepository( 'ProtonRigbagBundle:Circle' )->findAll();
				$circlesIds	= array();

				foreach( $circles as $c ) {
					$circlesIds[] = $c->getId();
				}

				$searchParams	= array();
			}


			$searchParams['limit']	= $listParams['pageSize'] * 2;
			$searchParams['offset']	= 0;

			$listParams['offset']	= $listParams['pageSize'] * 2;
			$listParams['circles']	= $circlesIds;

			$this->get( 'session' )->set( 'listParams', $listParams );

			$positions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->search( '', $searchParams );
			//$interests	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findForCircles( $circlesIds );
			$interests	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findAll();

			if( count( $positions ) == $searchParams['limit'] ) {
				$showLoadMore = 1;
			} else {
				$showLoadMore = 0;
			}

			$subaction	= $request->get( 'subAction', '' );
			$extraData	= array( 'type' => '' );
			$forceOpen	= false;

			switch( $subaction ) {
				case 'advertQuestion':
					$advertId	= $request->get( 'subId', '' );
					$advert		= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
					$extraData	= array(
										'type'		=> 'advertQuestion',
										'data'		=> $advert
									);
				break;
				case 'op':
					$forceOpen	= true;
				break;
			}
			
			if (count($interests) > 0) {
				$interest = $interests[0];
			} else {
				$interest = null;
			}
			
			if (count($circles) > 0) {
				$circle = $circles[0];
			} else {
				$circle = null;
			}

			$result		= array(
					'toUpdate'	=> array( 'headerTop', 'headerExtras', 'headerExtras-2', 'headerBottom', 'content', 'bodyClass' ),
					'header'	=> array(
							'top'		=> $engine->render( 'ProtonRigbagBundle:Qa:main-header-top.html.twig', array() ),
							'bottom'	=> $engine->render( 'ProtonRigbagBundle:Qa:main-header-bottom.html.twig', array( 'interests' => $interests ) ),
							'extras'	=> $engine->render( 'ProtonRigbagBundle:Qa:new-form-header.html.twig', array( 'circles' => $circles, 'fCircle' => $circle, 'extraData' => $extraData ) ),
							'extras2'	=> ''
					),
					'bodyClass'	=> 'q-and-a',
					'content'	=> $engine->render( 'ProtonRigbagBundle:Qa:main-content.html.twig', array( 'showLoadMore' => $showLoadMore, 'forceOpen' => $forceOpen, 'positions' => $positions, 'user' => $user ) )
			);


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}
}
