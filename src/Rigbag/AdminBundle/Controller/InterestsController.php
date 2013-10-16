<?php

namespace Rigbag\AdminBundle\Controller;

use Proton\RigbagBundle\Entity\Interest;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InterestsController extends AbstractController
{
	
	public function deleteAction( $interestId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$interest	= $em->getRepository('ProtonRigbagBundle:Interest')->find( $interestId );
	
		if( $interest ) {
			$em->remove( $interest );
			$em->flush();
		}
	
		return $this->redirect( $this->generateUrl( 'rb_admin_interests_list', array( 'page' => $request->get( 'page' ), 'q' => $request->get( 'q' ) ) ) );
	}
	
	public function addAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$interest		= new Interest();
	
		if( $request->getMethod() == 'POST' ) {
			
		
			
			if( $request->files->get( 'picture' ) ) {
			
				$upladedFile = $request->files->get( 'picture' );
				$newPath = '';
				
				$paths		= $this->container->getParameter( 'paths' );
				$newPath	= $paths['storage']['sport'];
				$fileName	= time() . '.' . $upladedFile->getExtension();
				
				$upladedFile->move( $newPath, $fileName );
				
				$interest->setPicture( $fileName );
			
			}
			else {
				$interest->setPicture( '' );
			}
			
			$interest->setName( $request->get( 'name' ) );
	
			$em->persist( $interest );
			$em->flush();
	
			return $this->redirect( $this->generateUrl( 'rb_admin_interests_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Interests:manage.html.twig', array( 'interest' => $interest ) );
	}
	
	public function editAction( $interestId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$interest		= $em->getRepository('ProtonRigbagBundle:Interest')->find( $interestId );
	
		if( $request->getMethod() == 'POST' ) {
			
			if( $request->files->get( 'picture' ) ) {
			
				$upladedFile = $request->files->get( 'picture' );
				$newPath = '';
				
				$paths		= $this->container->getParameter( 'paths' );
				$newPath	= $paths['storage']['sport'];
				$fileName	= time() . '.' . $upladedFile->getExtension();
				
				$upladedFile->move( $newPath, $fileName );
				
				$interest->setPicture( $fileName );
			
			}
			
			$interest->setName( $request->get( 'name' ) );
				
			$circles = $interest->getCircles();
				
			foreach( $circles as $circle ) {
				$circle->setName( $interest->getName() );
			}
				
			$em->flush();
				
			return $this->redirect( $this->generateUrl( 'rb_admin_interests_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Interests:manage.html.twig', array( 'interest' => $interest ) );
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
		$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:Interest')->search( $query, array(), array( 'force' => true ) ) ) / $perPage );
		$interests 	= $em->getRepository('ProtonRigbagBundle:Interest')->search( $query, array(), array( 'force' => true, 'offset' => $offset, 'limit' => $perPage ) );
			
		return $this->render('RigbagAdminBundle:Interests:list.html.twig', array( 'interests' => $interests, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
	
	}
	
	public function suggestAction( Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$query		= $request->get( 'query', null );
		$circles	= array();
		
		if( strlen( $query ) > 1 ) {
			$tmp = $em->getRepository( 'ProtonRigbagBundle:Interest' )->search( $query );
			$lp = 0;
			
			foreach( $tmp as $t ) {
				$circles[] = array(
								'id'		=> $t->getId(),
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
