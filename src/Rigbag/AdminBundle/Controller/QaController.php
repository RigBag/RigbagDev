<?php

namespace Rigbag\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QaController extends AbstractController
{
	
	public function deleteAction( $qaId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 	= $this->getDoctrine()->getManager();
		$qa		= $em->getRepository('ProtonRigbagBundle:QaPosition')->find( $qaId );
				
		if( $qa ) {
			if( $qa->getParentId() ) {
				$qaId = $qa->getParentId();
			}
			$em->remove( $qa );
			$em->flush();
		}
		if( isset( $qaId ) && $qaId ) {
			return $this->redirect( $this->generateUrl( 'rb_admin_qa_view', array( 'qaId' => $qaId ) ) );
		} else {
			return $this->redirect( $this->generateUrl( 'rb_admin_qa_list', array( 'page' => $request->get( 'page', 1 ), 'q' => $request->get( 'q', '' ) ) ) );
		}
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
		$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:QaPosition')->search( $query, array( 'state' => 'public' ) ) ) / $perPage );
		$qas 		= $em->getRepository('ProtonRigbagBundle:QaPosition')->search( $query, array( 'state' => 'public', 'offset' => $offset, 'limit' => $perPage ) );
		 
		return $this->render('RigbagAdminBundle:Qa:list.html.twig', array( 'qas' => $qas, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
	
	}
	
	public function viewAction( $qaId, Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$question	= $em->getRepository( 'ProtonRigbagBundle:QaPosition' )->find( $qaId );
		
		if( !$question || $question->getState() != 'public' ) {
			return $this->redirect( $this->generateUrl( 'rb_admin_qa_list' ) );
		} 
		
		
		return $this->render('RigbagAdminBundle:Qa:view.html.twig', array( 'question' => $question ) );
	}
	
	
}
