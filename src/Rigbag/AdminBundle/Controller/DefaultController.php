<?php

namespace Rigbag\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends AbstractController
{
    public function indexAction( Request $request )
    {
    	if( !$this->isLoged() ) {
    		return $this->redirectToLogin();
    	}
    	
    	$fromDate		= date( 'Y-m-d', strtotime( $request->get( 'from', date( 'Y-m-d' ) ) ) - ( $request->get( 'from' ) ? 0 : ( 24 * 60 * 60 * 30 ) ) );
    	$toDate			= date( 'Y-m-d', strtotime( $request->get( 'to', date( 'Y-m-d' ) ) ) );
    	
    	$em 		= $this->getDoctrine()->getManager();
    	
    	
    	
    	// PROFILES
    	
    	// Locations
   		 $query	= $em->getRepository( 'ProtonRigbagBundle:User')->createQueryBuilder('u')
    				->select( 'COUNT(u.id) as num, u.location_country_code' );
    	 
    	$query->groupBy( 'u.location_country_code' );
    	
    	$tmp 	= $query->getQuery()->getResult();
    	$profilesGeo	= array();
    	
    	foreach( $tmp as $t ) {
    		$profilesGeo[$t['location_country_code']]	= array( 'value' => $t['num'], 'key' => $t['location_country_code'] );
    	}
    	
    	// Num
    	$query	= $em->getRepository( 'ProtonRigbagBundle:User')->createQueryBuilder('u')
    				->select( 'COUNT(u.id) as num, u.created_at' );
    	 
    	$query 	= $query->where( 'u.created_at>=:fromDate' );
    	$query  = $query->andWhere( 'u.created_at<=:toDate' );
    	
    	$query->setParameter( 'fromDate', $fromDate );
    	$query->setParameter( 'toDate', $toDate );
    	
    	$query->groupBy( 'u.created_at' );
    	
    	$tmp 	= $query->getQuery()->getResult();
    	$tmp1	= array();
    	
    	foreach( $tmp as $t ) {
    		$key = date( 'Y-m-d', $t['created_at']->getTimestamp() );
    		if( key_exists( $key, $tmp1 ) ) {
    			$tmp1[$key] = $tmp1[$key] + $t['num'];
    		}
    		else {
    			$tmp1[$key] = $t['num'];
    		}
    	}
    	
    	$profiles = $this->fillDays( $tmp1, $fromDate, $toDate );
    	
    	
    	// ADVERTS
    	
    	$query	= $em->getRepository( 'ProtonRigbagBundle:Advert')->createQueryBuilder('u')
    					->select( 'COUNT(u.id) as num, u.created_at' );
    	
    	$query 	= $query->where( 'u.created_at>=:fromDate' );
    	$query  = $query->andWhere( 'u.created_at<=:toDate' );
    	 
    	$query->setParameter( 'fromDate', $fromDate );
    	$query->setParameter( 'toDate', $toDate );
    	 
    	$query->groupBy( 'u.created_at' );
    	 
    	$tmp 	= $query->getQuery()->getResult();
    	$tmp1	= array();
    	 
    	foreach( $tmp as $t ) {
    		$key = date( 'Y-m-d', $t['created_at']->getTimestamp() );
    		if( key_exists( $key, $tmp1 ) ) {
    			$tmp1[$key] = $tmp1[$key] + $t['num'];
    		}
    		else {
    			$tmp1[$key] = $t['num'];
    		}
    	}
    	
    	$adverts = $this->fillDays( $tmp1, $fromDate, $toDate );
    	
    	// ADVERTS: SALE
    	 
    	$query	= $em->getRepository( 'ProtonRigbagBundle:Advert')->createQueryBuilder('u')
    	->select( 'COUNT(u.id) as num, u.created_at' );
    	 
    	$query 	= $query->where( 'u.created_at>=:fromDate' );
    	$query  = $query->andWhere( 'u.created_at<=:toDate' );
    	$query  = $query->andWhere( 'u.mode=:mode' );
    	
    	$query->setParameter( 'fromDate', $fromDate );
    	$query->setParameter( 'toDate', $toDate );
    	$query->setParameter( 'mode', 'sale' );
    	
    	$query->groupBy( 'u.created_at' );
    	
    	$tmp 	= $query->getQuery()->getResult();
    	$tmp1	= array();
    	
    	foreach( $tmp as $t ) {
    		$key = date( 'Y-m-d', $t['created_at']->getTimestamp() );
    		if( key_exists( $key, $tmp1 ) ) {
    			$tmp1[$key] = $tmp1[$key] + $t['num'];
    		}
    		else {
    			$tmp1[$key] = $t['num'];
    		}
    	}
    	 
    	$advertsSale = $this->fillDays( $tmp1, $fromDate, $toDate );
    	
    	
    	// ADVERTS: SWAP
    	
    	$query	= $em->getRepository( 'ProtonRigbagBundle:Advert')->createQueryBuilder('u')
    	->select( 'COUNT(u.id) as num, u.created_at' );
    	
    	$query 	= $query->where( 'u.created_at>=:fromDate' );
    	$query  = $query->andWhere( 'u.created_at<=:toDate' );
    	$query  = $query->andWhere( 'u.mode=:mode' );
    	 
    	$query->setParameter( 'fromDate', $fromDate );
    	$query->setParameter( 'toDate', $toDate );
    	$query->setParameter( 'mode', 'swap' );
    	 
    	$query->groupBy( 'u.created_at' );
    	 
    	$tmp 	= $query->getQuery()->getResult();
    	$tmp1	= array();
    	 
    	foreach( $tmp as $t ) {
    		$key = date( 'Y-m-d', $t['created_at']->getTimestamp() );
    		if( key_exists( $key, $tmp1 ) ) {
    			$tmp1[$key] = $tmp1[$key] + $t['num'];
    		}
    		else {
    			$tmp1[$key] = $t['num'];
    		}
    	}
    	
    	$advertsSwap = $this->fillDays( $tmp1, $fromDate, $toDate );
    	
    	
    	// ADVERTS: FREEBIE
    	
    	$query	= $em->getRepository( 'ProtonRigbagBundle:Advert')->createQueryBuilder('u')
    	->select( 'COUNT(u.id) as num, u.created_at' );
    	
    	$query 	= $query->where( 'u.created_at>=:fromDate' );
    	$query  = $query->andWhere( 'u.created_at<=:toDate' );
    	$query  = $query->andWhere( 'u.mode=:mode' );
    	 
    	$query->setParameter( 'fromDate', $fromDate );
    	$query->setParameter( 'toDate', $toDate );
    	$query->setParameter( 'mode', 'freebie' );
    	 
    	$query->groupBy( 'u.created_at' );
    	 
    	$tmp 	= $query->getQuery()->getResult();
    	$tmp1	= array();
    	 
    	foreach( $tmp as $t ) {
    		$key = date( 'Y-m-d', $t['created_at']->getTimestamp() );
    		if( key_exists( $key, $tmp1 ) ) {
    			$tmp1[$key] = $tmp1[$key] + $t['num'];
    		}
    		else {
    			$tmp1[$key] = $t['num'];
    		}
    	}
    	
    	$advertsFreebie = $this->fillDays( $tmp1, $fromDate, $toDate );
    	
    	
    	
    	
    	
        return $this->render('RigbagAdminBundle:Default:index.html.twig', array(
        	'fromDate'		=> date( 'd.m.Y', strtotime($fromDate)),
        	'toDate'		=> date( 'd.m.Y', strtotime($toDate)),
        	'profiles'		=> $profiles,
        	'adverts'		=> $adverts,
        	'advertsSale'	=> $advertsSale,
        	'advertsSwap'	=> $advertsSwap,
        	'advertsFreebie'=> $advertsFreebie,
        	'profilesGeo'	=> $profilesGeo
        ) );
    }
    
    protected function fillDays( $data, $from, $to ) {
    	
    	$return = array();
    	$to = date( 'Y-m-d', strtotime( $to ) );
    	$from = date( 'Y-m-d', strtotime( $from ) );
    	$lp = 0;
    	while( $from <= $to ) {
    		if( key_exists( $from, $data ) ) {
    			$return[$from] = array(
    									'key'		=> $from,
    									'value'		=> $data[$from]
    					);
    		}
    		else {
    			$return[$from] = array(
    									'key'		=> $from,
    									'value'		=> 0
    					);
    		}
    		$ts = strtotime( $from );
    		$from = date( 'Y-m-d',   mktime( 0, 0, 0, date( 'm', $ts ), date( 'd', $ts ) + 1, date('Y', $ts) ) );
    		
    	}
    	
    	return $return;
    	
    }
}
