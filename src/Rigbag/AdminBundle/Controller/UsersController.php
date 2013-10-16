<?php

namespace Rigbag\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UsersController extends AbstractController
{
	
	public function listAction( Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
    	
    	$query 		= $request->get( 'q', '' );
    	$page		= $request->get( 'page', 1 );
    	$perPage	= 25;
    	$offset		= ( $page - 1 ) * $perPage;
    	$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:User')->search( $query ) ) / $perPage );
    	$users 		= $em->getRepository('ProtonRigbagBundle:User')->search( $query, array( 'offset' => $offset, 'limit' => $perPage, 'orderby' => 'u.created_at', 'orderdir' => 'DESC' ) );
    	
        return $this->render('RigbagAdminBundle:Users:list.html.twig', array( 'users' => $users, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
		
	}
	
	public function viewAction( $userId, Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
		$questions	= $em->getRepository('ProtonRigbagBundle:QaPosition')->search( '', array( 'state' => 'public', 'userId' => $user->getId() ) );
		
		return $this->render('RigbagAdminBundle:Users:view.html.twig', array( 'user' => $user, 'questions' => $questions ) );
	}
	
	public function editAction( $userId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
	
		if( $request->getMethod() == 'POST' ) {
			
			$user->setName( $request->get( 'name' ) )
				->setEmail( $request->get( 'email' ) )
				->setState( $request->get( 'state' ) )
				->setPhone( $request->get( 'phone' ) )
				->setLocation( $request->get( 'location' ) )
				->setLocationLat( $request->get( 'locationLat' ) )
				->setLocationLng( $request->get( 'locationLng' ) )
				->setLocationFormated( $request->get( 'locationFormatted' ) )
				->setLocationCountryCode( $request->get( 'locationCountryCode' ) )
				->setBio( $request->get( 'bio' ) )
				->setPaypalId( $request->get( 'paypalId' ) );
                        if($request->get('paymentMod' )!= NULL) {
                           $user->setPaymentMode(1);
                        } else
                        {
                            $user->setPaymentMode(0);
                        }
                       
			// Circles
			$circlesIds	= $request->get( 'circle', array() );
			$rmCircles 	= array();
			
			foreach( $user->getCircles() as $circle ) {
				if( !in_array( $circle->getId(), $circlesIds ) ) {
					$rmCircles[] = $circle;
				}
			}
			
			foreach( $rmCircles as $circle ) {
				$user->removeCircle( $circle );
			}
			
			$newCircles		= $request->get( 'circleNew', null );
			if( !is_null( $newCircles ) ) {
				$newCircles = json_decode( $newCircles );
				 
				foreach( $newCircles as $cid ) {
					$circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $cid );
					$user->addCircle( $circle );
				}
			}
			
			// Interests
			$interestsIds	= $request->get( 'interest', array() );
			$rmInterests 	= array();
			
			foreach( $user->getInterests() as $interest ) {
				if( !in_array( $interest->getId(), $interestsIds ) ) {
					$rmInterests[] = $interest;
				}
			}
			
			foreach( $rmInterests as $interest ) {
				$user->removeInterest( $interest );
			}
			
			$newInterests		= $request->get( 'interestNew', null );
			if( !is_null( $newInterests ) ) {
				$newInterests = json_decode( $newInterests );
				 
				foreach( $newInterests as $cid ) {
					$interest = $em->getRepository( 'ProtonRigbagBundle:Interest' )->find( $cid );
					$user->addInterest( $interest );
				}
			}
                       
			$em->flush();
			
			return $this->redirect( $this->generateUrl( 'rb_admin_profiles_list' ) ); 
		}
		
		
		return $this->render('RigbagAdminBundle:Users:manage.html.twig', array( 'user' => $user ) );
	}
	
	public function deleteAction( $userId, Request $request ) {
	
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$user		= $em->getRepository('ProtonRigbagBundle:User')->find( $userId );
	
		if( $user ) {
			$em->remove( $user );
			$em->flush();
		}
	
		return $this->redirect( $this->generateUrl( 'rb_admin_profiles_list' ) );
		
	}
	
	
}
