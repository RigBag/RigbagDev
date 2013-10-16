<?php

namespace Proton\RigbagBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\Location;
use Proton\RigbagBundle\Entity\Circle;

class MailController extends \ProtonLabs_Controller
{
	public function suggestAdvertToFriendAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$advertId	= $request->get( 'advertId', 0 );
			$msg		= $request->get( 'message', '' );
			$userId		= $this->getUserId();
			$emails		= explode( ',', str_replace( "\n", '', $request->get( 'emails', '' ) ) );
		
			$result		= $this->suggestAdvertToFriend( $advertId, $msg, $emails, $userId );
				
			$response 	= new JsonResponse( $result, 200 );
			$callback 	= $request->get( 'callback', null );
			
			if( $callback ) {
				$response->setCallback( $callback );
			}
		
			return $response;
		}
		
		return $this->blackholeResponse();
	 
	}
	
	public function sendAction( Request $request ) {
	
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$type		= $request->get( 'type', '' );
			$data		= $request->get( 'data', array() );
			
			$result		= $this->send( $type, $data );
			
			$response = new JsonResponse( $result, 200 );
			$callback = $request->get( 'callback', null );
			
			if( $callback ) {
				$response->setCallback( $callback );
			}
	
			return $response;
		}
	
		return $this->blackholeResponse();
	
	}
}
