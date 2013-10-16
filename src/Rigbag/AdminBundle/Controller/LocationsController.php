<?php

namespace Rigbag\AdminBundle\Controller;

use Proton\RigbagBundle\Entity\Location;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LocationsController extends AbstractController
{
	
	public function deleteAction( $locationId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$location		= $em->getRepository('ProtonRigbagBundle:Location')->find( $locationId );
	
		if( $location ) {
			$em->remove( $location );
			$em->flush();
		}
	
		return $this->redirect( $this->generateUrl( 'rb_admin_locations_list', array( 'page' => $request->get( 'page' ), 'q' => $request->get( 'q' ) ) ) );
	}
	
	public function addAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$location		= new Location();
	
		if( $request->getMethod() == 'POST' ) {
			$location->setName( $request->get( 'name' ) )
				->setLat( $request->get( 'lat' ) )
				->setLng( $request->get( 'lng' ) )
				->setCode( $request->get( 'code' ) );
				
			$em->persist( $location );
			
			$em->flush();
				
			return $this->redirect( $this->generateUrl( 'rb_admin_locations_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Locations:manage.html.twig', array( 'location' => $location ) );
	}
	
	public function editAction( $locationId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$location		= $em->getRepository('ProtonRigbagBundle:Location')->find( $locationId );
	
		if( $request->getMethod() == 'POST' ) {
			$location->setName( $request->get( 'name' ) )
					->setLat( $request->get( 'lat' ) )
					->setLng( $request->get( 'lng' ) )
					->setCode( $request->get( 'code' ) );
			
			$circles = $location->getCircles();
			
			foreach( $circles as $circle ) {
				$circle->setDescription( $location->getName() );
			}
			
			$em->flush();
			
			return $this->redirect( $this->generateUrl( 'rb_admin_locations_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Locations:manage.html.twig', array( 'location' => $location ) );
	}
	
	public function listAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		 
		$query 		= $request->get( 'q', '' );
		$page		= $request->get( 'page', 1 );
		$perPage	= 25;
		$offset		= ( $page - 1 ) * $perPage;
		$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:Location')->search( $query, array() ) ) / $perPage );
		$locations 	= $em->getRepository('ProtonRigbagBundle:Location')->search( $query, array( 'offset' => $offset, 'limit' => $perPage ) );
		 
		return $this->render('RigbagAdminBundle:Locations:list.html.twig', array( 'locations' => $locations, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
	
	}
	
	
	public function suggestAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$query		= $request->get( 'query', null );
		$locations	= array();
	
		if( strlen( $query ) > 1 ) {
			$tmp = $em->getRepository( 'ProtonRigbagBundle:Location' )->search( $query );
			$lp = 0;
				
			foreach( $tmp as $t ) {
				$locations[] = array(
						'id'		=> $t->getId(),
						'name'		=> $t->getName()
				);
				$lp++;
				if( $lp > 20 ) {
					break;
				}
			}
		}
	
		$response = new JsonResponse( $locations );
		return $response;
	}
	
	
	
}
