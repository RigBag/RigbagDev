<?php

namespace Proton\RigbagBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\Location;
use Proton\RigbagBundle\Entity\Circle;

class CirclesController extends \ProtonLabs_Controller
{

	public function browseAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em			= $this->getDoctrine()->getManager();

			$sportId 	= $request->get('sportId', null );
			$locationId = $request->get( 'locationId', null );

			$user 		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			$sports 	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findBy( array(), array( 'name' => 'asc' ) );
			$locations  = $em->getRepository( 'ProtonRigbagBundle:Location' )->findBy( array(), array( 'name' => 'asc' ) );
			$myCircles  = $user->getCircles();
			$circles 	= $this->loadCircles( $sportId, $locationId );

			$engine 	= $this->container->get('templating');


			$result		= array(
						'toUpdate'	=> array( 'content', 'bodyClass', 'headerTop', 'headerExtras', 'headerBottom' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => false ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:browse-header-bottom.html.twig', array() ),
								'extras'	=> ''
						),
						'bodyClass'	=> 'circles browse',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:browse-content.html.twig', array( 'circles' => $circles, 'myCircles' => $myCircles, 'sports' => $sports, 'locations' => $locations ) )
				);


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function listAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}
			$circles 	= $this->loadCircles( $request->get( 'sportId', null ), $request->get( 'locationId', null ) );
			$engine 	= $this->container->get('templating');
			$result['content']	= $engine->render( 'ProtonRigbagBundle:Circle:browse-sub-content.html.twig', array( 'circles' => $circles ) );
			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	protected function loadCircles( $sportId = null, $locationId = null, $offset = 0 ) {

		$em			= $this->getDoctrine()->getManager();
		$crit 		= array();
		if( !is_null( $sportId ) && $sportId ) {
			$crit['interest_id'] = $sportId;
		}
		if( !is_null( $locationId ) && $locationId ) {
			$crit['location_id'] = $locationId;
		}
		$circles = array();
		$user 		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
		$tmp 		= $user->getCircles();
		$uc 		= array();
		foreach( $tmp as $t ) { $uc[] = $t->getId(); };
		$tmp 		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->findBy( $crit, array( 'name' => 'asc', 'description' => 'asc' ) ); //, 20, $offset );
		$circles 	= array();
		foreach( $tmp as $t ) {
			if( !in_array( $t->getId(), $uc ) ) {
				$circles[] = $t;
			}
		}
		return $circles;
	}

	public function addAction( $circleId, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em		= $this->getDoctrine()->getManager();

			$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			if (!$user->hasCircle($circle)) {
				$user->addCircle( $circle );
				$em->flush();	
			}

			$result		= array('success' => true);

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function deleteAction( $circleId, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em		= $this->getDoctrine()->getManager();

			$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			$user->removeCircle( $circle );

			$em->flush();

			$result		= array('success' => true);

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function joinSearchAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em		= $this->getDoctrine()->getManager();

			$missCircles	= array();
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$circles	= $user->getCircles();
			foreach( $circles as $circle ) {
				$missCircles[]	= $circle->getId();
			}

			$tmp		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->search( $request->get( 'q', '' ), $missCircles );
			$circles	= $tmp;


			$engine 	= $this->container->get('templating');


			$result		= array(
					'toUpdate'	=> array( 'subContent' ),
					'subContent'	=> $engine->render( 'ProtonRigbagBundle:Circle:content-join-search.html.twig', array( 'circles' => $circles ) )
			);

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function joinAction( $mode, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$myProfile	= true;



			if( $mode == 'simple' ) {
				$result		= array(
						'toUpdate'	=> array( 'content' ),
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:content-join.html.twig', array(  ) )
				);
			} else {
				$result		= array(
						'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:User:profile-header-top.html.twig', array( 'myProfile' => $myProfile, 'user' => $user ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:User:profile-header-bottom.html.twig', array( 'type' => 'circles', 'myProfile' => $myProfile, 'user' => $user ) )
						),
						'bodyClass'	=> 'my-profile',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:content-join.html.twig', array(  ) )
				);
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
			$circles	= $user->getCircles();
			$circlesIds	= array();
			foreach( $circles as $circle ) {
				$circlesIds[]	= $circle->getId();
			}
			$queryParams		= array( 'mode' => $mode, 'circles' => $circlesIds, 'limit' => $params['pageSize'], 'offset' => $params['offset'] );
			$adverts			= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( '', $queryParams );
		} else {
			$user				= new User();
			$queryParams		= array( 'mode' => $mode, 'limit' => $params['pageSize'], 'offset' => $params['offset'] );
			$adverts			= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( '', $queryParams );
		}

		return array( 'user' => $user, 'adverts' => $adverts );
	}

	public function moreAdvertsAction( Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$listParams 	= $this->get( 'session' )->get( 'listParams', null );
			$em 			= $this->getDoctrine()->getManager();
			$engine 		= $this->container->get('templating');
			$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
			$circle			= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $listParams['circleId'] );

			$searchParams	= array( 'circles' => array( $circle->getId() ), 'limit' => $listParams['pageSize'], 'offset' => $listParams['offset'] );

			$listParams['offset']	= $listParams['pageSize'] + $listParams['offset'];
			$this->get( 'session' )->set( 'listParams', $listParams );

			$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( '', $searchParams );

			if( count( $adverts ) == $listParams['pageSize'] ) {
				$full	= 1;
			} else {
				$full	= 0;
			}

			$result['content']	= $engine->render( 'ProtonRigbagBundle:Advert:list-more.html.twig', array( 'adverts' => $adverts ) );
			$result['full']		= $full;

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();
	}

	public function advertsAction( $circleId, $mode, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$listParams	= array(
					'offset'	=> 0,
					'pageSize'	=> 6
			);

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			if( !$circleId ) {
				$circles	= $user->getCircles();
				$circle		= null;
				foreach( $circles as $circle ) {
					break;
				}
			} else {
				$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			}

			$listParams['circleId']	= $circle->getId();

			if( !$circle ) {
				$searchParams	= array( 'limit' => ( $listParams['pageSize'] * 2 ) );
			} else {
				$searchParams	= array( 'circles' => array( $circle->getId() ), 'limit' => ( $listParams['pageSize'] * 2 ) );
			}

			$listParams['offset']	= $listParams['pageSize'] * 2;
			$this->get( 'session' )->set( 'listParams', $listParams );

			$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->search( '', $searchParams );
			$full		= ( $listParams['offset'] == count( $adverts ) ? 1 : 0 );

			if( $mode == 'simple' ) {

				$result		= array(
						'toUpdate'	=> array( 'circleContent', 'headerTop', 'headerExtras', 'headerBottom', 'bodyClass', 'circleContentClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-header-extras.html.twig', array() )
						),
						'bodyClass'	=> 'circles',
						'contentClass'	=> 'adverts-list',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-content.html.twig', array( 'showLoadMore' => $full, 'adverts' => $adverts ) )
				);

			} else {

				$result		= array(
						'toUpdate'	=> array( 'content', 'headerTop', 'headerExtras', 'headerBottom', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:adverts-header-extras.html.twig', array() )
						),
						'bodyClass'	=> 'circles',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:main-content.html.twig', array( 'showLoadMore' => $full, 'type' => 'adverts', 'circles' => $user->getCircles(), 'currentCircle' => $circle, 'adverts' => $adverts ) )
				);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function membersAction( $circleId, $mode, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 		= $this->getDoctrine()->getManager();
			$engine 	= $this->container->get('templating');
			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			if( !$circleId ) {
				$circles	= $user->getCircles();
				$circle		= null;
				foreach( $circles as $circle ) {
					break;
				}
			} else {
				$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			}

			$members = $circle->getUsers();

			if( $mode == 'simple' ) {

				$result		= array(
						'toUpdate'	=> array( 'circleContent', 'headerExtras', 'headerBottom', 'bodyClass', 'circleContentClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-header-extras.html.twig', array() )
						),
						'bodyClass'		=> 'circles',
						'contentClass'	=> 'circle-members',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-content.html.twig', array( 'members' => $members ) )
				);

			} else {

				$result		= array(
						'toUpdate'	=> array( 'content', 'headerTop', 'headerExtras', 'headerBottom', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:members-header-extras.html.twig', array() )
						),
						'bodyClass'	=> 'circles',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:main-content.html.twig', array( 'type' => 'members', 'circles' => $user->getCircles(), 'currentCircle' => $circle, 'members' => $members ) )
				);
			}

			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

	public function qaAction( $circleId, $mode, Request $request ) {

		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			if( !$this->auth() ) {
				return $this->unAuthResponse();
			}

			$em 	= $this->getDoctrine()->getManager();

			$engine 	= $this->container->get('templating');

			$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			$interests	= $em->getRepository( 'ProtonRigbagBundle:Interest' )->findAll();
			$locations	= $em->getRepository( 'ProtonRigbagBundle:Location' )->findAll();

			foreach( $interests as $interest ) {
				break;
			}
			foreach( $locations as $location ) {
				break;
			}

			$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

			if( !$circleId ) {
				$circles	= $user->getCircles();
				$circle		= null;
				foreach( $circles as $circle ) {
					break;
				}
			} else {
				$circle		= $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $circleId );
			}


			$questions	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->search( '', array( 'circles' => array( $circle->getId() ) ) );

			if( $mode == 'simple' ) {

				$result		= array(
						'toUpdate'	=> array( 'circleContent', 'headerExtras', 'headerBottom', 'bodyClass', 'circleContentClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:qa-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:qa-header-extras.html.twig', array( 'circle' => $circle, 'interests' => $interests, 'locations' => $locations, 'fInterest' => $interest, 'fLocation' => $location ) )
						),
						'bodyClass'		=> 'circles',
						'contentClass'	=> 'q-and-a-list',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:qa-content.html.twig', array( 'currentCircle' => $circle, 'questions' => $questions ) )
				);

			} else {

				$result		= array(
						'toUpdate'	=> array( 'content', 'headerTop', 'headerExtras', 'headerBottom', 'bodyClass' ),
						'header'	=> array(
								'top'		=> $engine->render( 'ProtonRigbagBundle:Circle:main-header-top.html.twig', array( 'circle' => $circle, 'has' => $user->hasCircle( $circle ) ) ),
								'bottom'	=> $engine->render( 'ProtonRigbagBundle:Circle:qa-header-bottom.html.twig', array( 'circle' => $circle ) ),
								'extras'	=> $engine->render( 'ProtonRigbagBundle:Circle:qa-header-extras.html.twig', array(  'circle' => $circle, 'interests' => $interests, 'locations' => $locations, 'fInterest' => $interest, 'fLocation' => $location ) )
						),
						'bodyClass'	=> 'circles',
						'content'	=> $engine->render( 'ProtonRigbagBundle:Circle:main-content.html.twig', array( 'type' => 'qa', 'circles' => $user->getCircles(), 'currentCircle' => $circle, 'questions' => $questions ) )
				);
			}


			$result['actionStamp']		= $request->get( 'actionStamp', '' );

			return new JsonResponse( $result, 200 );
		}

		return $this->blackholeResponse();

	}

}
