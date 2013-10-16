<?php

namespace Rigbag\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AbstractController extends Controller
{
  
	protected function getAdmin() {
		
		if( $this->isLoged() ) {
			$em 			= $this->getDoctrine()->getManager();
			$admin		= $em->getRepository( 'RigbagAdminBundle:Admin' )->find( $this->getAdminId() );
			return $admin;
		}
		return null;
	}
	
	protected function getAdminId() {
		$sesData		= $this->get('session')->get('adminData');
		if( isset( $sesData['id'] ) ) {
			return $sesData['id'];
		}
		return null;
	}
	
	public function isLoged() {
    	return $this->get('session')->get( 'isAdminLoged', false );
    }
    
    public function redirectToLogin() {
    	return $this->redirect( $this->generateUrl( 'rb_admin_login' ) );
    }
    
}
