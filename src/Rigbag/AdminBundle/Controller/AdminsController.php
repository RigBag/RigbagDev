<?php

namespace Rigbag\AdminBundle\Controller;

use Rigbag\AdminBundle\Entity\Admin;

use Proton\RigbagBundle\Entity\Location;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminsController extends AbstractController
{
	
	public function deleteAction( $adminId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 			= $this->getDoctrine()->getManager();
		$admin		= $em->getRepository('RigbagAdminBundle:Admin')->find( $adminId );
	
		if( $admin && $admin->getId() != $this->getAdminId() ) {
			$em->remove( $admin );
			$em->flush();
		}
	
		return $this->redirect( $this->generateUrl( 'rb_admin_admins_list', array( 'page' => $request->get( 'page' ), 'q' => $request->get( 'q' ) ) ) );
	}
	
	public function addAction( Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$admin		= new Admin();
	
		if( $request->getMethod() == 'POST' ) {
			$admin->setName( $request->get( 'name' ) )
					->setEmail( $request->get( 'email' ) )
					->setPassword( md5( $request->get( 'password' ) ) );
				
			$em->persist( $admin );
			
			$em->flush();
				
			return $this->redirect( $this->generateUrl( 'rb_admin_admins_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Admins:manage.html.twig', array( 'admin' => $admin ) );
	}
	
	public function editAction( $adminId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$admin		= $em->getRepository( 'RigbagAdminBundle:Admin' )->find( $adminId );
	
		if( $request->getMethod() == 'POST' ) {
			$admin->setName( $request->get( 'name' ) )
					->setEmail( $request->get( 'email' ) );
			
			if( $request->get( 'password' ) ) {
				$admin->setPassword( md5( $request->get( 'password' ) ) );
			}
			
			$em->flush();
				
			return $this->redirect( $this->generateUrl( 'rb_admin_admins_list' ) );
		}
	
		return $this->render('RigbagAdminBundle:Admins:manage.html.twig', array( 'admin' => $admin ) );
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
		$lastPage	= ceil( count( $em->getRepository('RigbagAdminBundle:Admin')->search( $query, array() ) ) / $perPage );
		$admins 	= $em->getRepository('RigbagAdminBundle:Admin')->search( $query, array( 'offset' => $offset, 'limit' => $perPage ) );
		 
		return $this->render('RigbagAdminBundle:Admins:list.html.twig', array( 'admins' => $admins, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
	
	}
	
}
