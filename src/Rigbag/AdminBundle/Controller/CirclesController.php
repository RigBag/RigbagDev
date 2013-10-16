<?php

namespace Rigbag\AdminBundle\Controller;

use Proton\RigbagBundle\Entity\Circle;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CirclesController extends AbstractController
{
	
	public function createAction( Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		if( $request->getMethod() == 'POST' ) {

			$em 		= $this->getDoctrine()->getManager();
			
			$interest		= $request->get( 'interest', null );
			//$location		= $request->get( 'location', null );
			//var_dump($interest);
                        //echo "Hhi"; exit;
			if( !is_null( $interest ) ) {
				$interests	= json_decode( $interest );
				//$locations	= json_decode( $location );
				
				
				foreach( $interests as $iid ) {
					$interest = $em->getRepository( 'ProtonRigbagBundle:Interest' )->find( $iid );
//					foreach( $locations as $lid ) {
//						$location = $em->getRepository( 'ProtonRigbagBundle:Location' )->find( $lid );
//						//$circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->findOneBy( array( 'location_id' => $lid, 'interest_id' => $iid ) );
                                                $circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->findOneBy( array( 'interest_id' => $iid ) );

						if( !$circle ) {
							$circle = new Circle();
//							$circle->setDescription( $location->getName())
									$circle->setName( $interest->getName() )
//									->setLocation( $location )
									->setInterest( $interest );
							$em->persist( $circle );
						}
//					}
				}
				
				$em->flush();
				
				return $this->redirect( $this->generateUrl( 'rb_admin_circles_list' ) );
			}
		}
		
		return $this->render('RigbagAdminBundle:Circles:create.html.twig', array() );
		
	}
	
	public function deleteAction( $circleId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$circle		= $em->getRepository('ProtonRigbagBundle:Circle')->find( $circleId );
	
		if( $circle ) {
			$em->remove( $circle );
			$em->flush();
		}
	
		return $this->redirect( $this->generateUrl( 'rb_admin_circles_list', array( 'page' => $request->get( 'page' ), 'q' => $request->get( 'q' ) ) ) );
	}
	
	public function listAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		 
		$query 		= $request->get( 'q', '' );
               // print_r($query); exit;
		$page		= $request->get( 'page', 1 );
		$perPage	= 25;
		$offset		= ( $page - 1 ) * $perPage;
		$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:Circle')->search( $query, array(), array( 'force' => true ) ) ) / $perPage );
		$circles 	= $em->getRepository('ProtonRigbagBundle:Circle')->search( $query, array(), array( 'force' => true, 'offset' => $offset, 'limit' => $perPage ) );
		// print_r($circles);
		return $this->render('RigbagAdminBundle:Circles:list.html.twig', array( 'circles' => $circles, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
	
	}
	
	public function suggestAction( Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$query		= $request->get( 'query', null );
		$circles	= array();
		
		if( strlen( $query ) > 1 ) {
			$tmp = $em->getRepository( 'ProtonRigbagBundle:Circle' )->search( $query );
			$lp = 0;
			
			foreach( $tmp as $t ) {
				$circles[] = array(
								'id'		=> $t->getId(),
//								'name'		=> $t->getName() . ' (' . $t->getDescription() . ')'
                                                                'name'		=> $t->getName()
							);
				$lp++;
				if( $lp > 20 ) {
					break;
				}
			}
		} 
		
		$response = new JsonResponse( $circles );
		return $response;
	}
	
	
}
