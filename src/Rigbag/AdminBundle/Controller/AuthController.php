<?php

namespace Rigbag\AdminBundle\Controller;

use Rigbag\AdminBundle\Entity\Admin;

use Proton\RigbagBundle\Entity\Location;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuthController extends AbstractController
{
	
	public function loginAction( Request $request ) {
		
		if( $request->getMethod() == 'POST' ) {
			
			$em 		= $this->getDoctrine()->getManager();
			
			$admin		= $em->getRepository( 'RigbagAdminBundle:Admin' )->findOneBy( array( 'email' => $request->get( 'email' ), 'password' => md5( $request->get( 'password' ) ) ) );
			
			if( $admin ) {
				
				$this->get('session')->set( 'isAdminLoged', true );
				$adminData	= array(
								'id'		=> $admin->getId(),
								'name'		=> $admin->getName(),
								'email'		=> $admin->getEmail()
							);
				
				$this->get('session')->set('adminData', $adminData );
				
				return $this->redirect( $this->generateUrl( 'rb_admin_dashboard' ) );
			}
		}
		
		return $this->render('RigbagAdminBundle:Auth:login.html.twig', array() );
	}
	
	public function logoutAction( Request $request ) {
		
		$adminData	= array();
		$this->get('session')->set( 'isAdminLoged', false );
		$this->get('session')->set('adminData', $adminData );
		
		return $this->redirect( $this->generateUrl( 'rb_admin_login' ) );
	}
	
}
