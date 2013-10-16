<?php

namespace Rigbag\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdvertsController extends AbstractController
{
	
	public function deleteAction( $advertId, Request $request ) {
		
		if( !$this->isLoged() ) {
			return $this->redirectToLogin();
		}
		
		$em 		= $this->getDoctrine()->getManager();
		$advert		= $em->getRepository('ProtonRigbagBundle:Advert')->find( $advertId );
		
		if( $advert ) {
			$em->remove( $advert );
			$em->flush();
		}
		
		return $this->redirect( $this->generateUrl( 'rb_admin_adverts_list', array( 'page' => $request->get( 'page' ), 'q' => $request->get( 'q' ) ) ) );
	}
	
	
    public function listAction( Request $request )
    {
    	if( !$this->isLoged() ) {
    		return $this->redirectToLogin();
    	}
    	
    	$em 		= $this->getDoctrine()->getManager();
    	
    	$query 		= $request->get( 'q', '' );
    	$page		= $request->get( 'page', 1 );
    	$perPage	= 25;
    	$offset		= ( $page - 1 ) * $perPage;
    	$lastPage	= ceil( count( $em->getRepository('ProtonRigbagBundle:Advert')->search( $query, array( 'states' => array( 'all' ) ) ) ) / $perPage );
    	$adverts 	= $em->getRepository('ProtonRigbagBundle:Advert')->search( $query, array( 'states' => array( 'all' ), 'offset' => $offset, 'limit' => $perPage ) );
    	
        return $this->render('RigbagAdminBundle:Adverts:list.html.twig', array( 'adverts' => $adverts, 'query' => $query, 'pagination' => array( 'actual' => $page, 'lastPage' => $lastPage ) ) );
    }
    
    public function viewAction( $advertId, Request $request )
    {
    	if( !$this->isLoged() ) {
    		return $this->redirectToLogin();
    	}
    	
    	$em 		= $this->getDoctrine()->getManager();
    	$advert		= $em->getRepository('ProtonRigbagBundle:Advert')->find( $advertId );
    	 
    	 
    	return $this->render('RigbagAdminBundle:Adverts:view.html.twig', array( 'advert' => $advert ) );
    }
    
    public function editAction( $advertId, Request $request )
    {
    	if( !$this->isLoged() ) {
    		return $this->redirectToLogin();
    	}
    	
    	$em 		= $this->getDoctrine()->getManager();
    	$advert		= $em->getRepository('ProtonRigbagBundle:Advert')->find( $advertId );
    	if( $request->getMethod() == 'POST' ) {
    		
    		$condition = $em->getRepository( 'ProtonRigbagBundle:DictionaryValue' )->find( $request->get('condition') );
    		
    		$advert->setTitle( $request->get('title') )
    			->setLocation( $request->get('location') )
    			->setLocationFormated( $request->get('locationFormatted') )
    			->setLocationLat( $request->get('locationLat'))
    			->setLocationLng( $request->get('locationLng'))
    			->setCondition( $condition )
    			->setState( $request->get( 'state' ) );
    		
    		if( $advert->getMode() == 'sale' ) {
    			$advert->setPrice( $request->get( 'price' ) )
    					->setCurrency( $request->get( 'currency' ) )
    					->setPaypalId( $request->get( 'paypalId' ) );
    		}
    		elseif ( $advert->getMode() == 'swap' ) {
    			$advert->setSwapFor( $request->get( 'swapFor' ) );
    		}
    		
    		$circlesIds	= $request->get( 'circle', array() );
    		$rmCircles 	= array();
    		
    		foreach( $advert->getCircles() as $circle ) {
    			if( !in_array( $circle->getId(), $circlesIds ) ) {
    				$rmCircles[] = $circle;
    			}
    		}
    		
    		foreach( $rmCircles as $circle ) {
    			$advert->removeCircle( $circle );
    		}
    		
    		$newCircles		= $request->get( 'circleNew', null );
    		if( !is_null( $newCircles ) ) {
    			$newCircles = json_decode( $newCircles );
    			
    			foreach( $newCircles as $cid ) {
    				$circle = $em->getRepository( 'ProtonRigbagBundle:Circle' )->find( $cid );
    				$advert->addCircle( $circle );
    			}
    		}
    		
    		$oldPhotos	= $request->get( 'photo', array() );
    		$rmPhotos	= array();
    		
    		foreach( $advert->getImages() as $img ) {
    			if( !in_array( $img->getId(), $oldPhotos ) ) {
    				$rmPhotos[]	= $img;
    			}
    		}
    		
    		
    		foreach( $rmPhotos as $img ) {
    			$em->remove( $img );
    		}
    		
    		
    		$em->flush();
    		return $this->redirect( $this->generateUrl( 'rb_admin_adverts_list' ) );
    		
    		
    		
    	}
    	
    	
    	$dictionary	= $em->getRepository( 'ProtonRigbagBundle:Dictionary' )->findOneBy( array( 'code' => 'item_condition' ) );
    	$conditions	= $em->getRepository( 'ProtonRigbagBundle:DictionaryValue' )->findForDictionary( $dictionary );
    	$currencies	= \Proton\RigbagBundle\Entity\Transaction::getCurrencies();
    	
    	return $this->render('RigbagAdminBundle:Adverts:manage.html.twig', array( 
    																			'advert' 		=> $advert,
    																			'currencies'	=> $currencies,
    																			'conditions'	=> $conditions
    																	) );
    }
}
