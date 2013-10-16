<?php

namespace Proton\RigbagBundle\Controller;

use Proton\RigbagBundle\Entity\Transaction;

use Proton\RigbagBundle\Repository\QaPositionRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\Location;
use Proton\RigbagBundle\Entity\Circle;
use Proton\RigbagBundle\Entity\QaPosition;
use Proton\RigbagBundle\Entity\UserOption;

class SandboxController extends \ProtonLabs_Controller
{
	
	
	public function selectPaymentAction( $return, Request $request ) {
		
		$em 		= $this->getDoctrine()->getManager();
		$result		= array();
		$engine 	= $this->container->get('templating');
		
		switch( $return ) {
			case 'advert':
				$returnData	= $request->get( 'advertId', '' );
			break;
			default:
				$returnData	= '';
		}
		
		$types		= \Proton\RigbagBundle\Entity\Transaction::getTypes();
		$methods	= \Proton\RigbagBundle\Entity\Transaction::getMethods();
		$currencies	= \Proton\RigbagBundle\Entity\Transaction::getCurrencies();
		
		$result		= array(
								'toUpdate'	=> array( 'content' ),
								'content'	=> $engine->render( 'ProtonRigbagBundle:Sandbox:select-payment.html.twig', array( 
																																'types'			=> $types,
																																'methods'		=> $methods,
																																'currencies'	=> $currencies,
																																'return'		=> $return,
																																'returnData'	=> $returnData
																														) )
						);
		
		
		$result['actionStamp']		= $request->get( 'actionStamp', '' );
		
		return $this->jsonResponse( $result );
		
	}
	
	public function processPaymentAction( Request $request ) {
		
		$em 		= $this->getDoctrine()->getManager();
		
		$userSourceId		= $request->get( 'userSourceId', $this->getUserId() );
		$userDestinationId	= $request->get( 'userDestinationId', null );
		$advertId			= $request->get( 'advertId', null );
		$amount				= $request->get( 'amount', null );
		$currency			= $request->get( 'currency', null );
		$type				= $request->get( 'type', 'advert' );
		$method				= $request->get( 'method', 'paypal' );
		$state				= $request->get( 'result', '200' );
		
		if( $request->get( 'ret') == 'advert' ) {
			$advertId			= $request->get( 'retData' );
			$advert				= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
			$userDestinationId	= $advert->getUserId();
		}
		
		
		$userSource			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userSourceId );
		
		if( $userDestinationId ) {
			$userDestination	= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userDestinationId );
		} else {
			$userDestination	= null;
		}
		
		if( $advertId ) {
			$advert				= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
		} else {
			$advert				= null;
		}
		
		$description			= \Proton\RigbagBundle\Entity\Transaction::prepareDescription(
																								$userSource,
																								$userDestination,
																								$advert,
																								$amount,
																								$currency,
																								$type,
																								$method,
																								$state
																							);
		
		$transaction			= new Transaction();
		
		$transaction->setAdvert( $advert )
					->setUserPay( $userSource )
					->setUserReceive( $userDestination )
					->setAmount( $amount )
					->setCurrency( $currency )
					->setType( $type )
					->setMethod( $method )
					->setState( $state )
					->setDescription( $description );
		
		$em->persist( $transaction );
		
		$em->flush();

		switch( $type ) {
			case 'subscription':
				if( $request->get( 'ret') == 'subscription' ) {
					if( $state == 200 ) {
						$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );
						
						$expDate		= new \DateTime();
						$expDate->setTimestamp( time() + ( 60 * 60 * 24 * 365 ) );
						
						$user->setAccountType( 'annual' );
						$user->setExpiredAt( $expDate );
						
						$subscriptionFilled	= $user->getOptionValue( 'subscription_filled' );
						if( !$subscriptionFilled ) {
							$uo	= new UserOption();
							$uo->setOptionKey( 'subscription_filled' )
									->setOptionValue( date('Y-m-d H:i:s' ) )
									->setUser( $user );
						
							$user->addOption( $uo );
						
							$em->persist( $uo );
						}
	
						$em->flush();
					}
				}
			break;
			case 'advert':
				if( $request->get( 'ret') == 'advert' ) {
					if( $state == 200 ) {
						
						$advert->setState( 'closed' ); 
						
						$em->flush();
						$this->get('session')->set( 'flashMessage', array( 'type' => 'success', 'title' => 'Congratulation!!!', 'content' => 'You have just bought this item.' ) );
					}
					else {
						$this->get('session')->set( 'flashMessage', array( 'type' => 'error', 'title' => 'Upssss!!!', 'content' => 'Something is worng. You can\'t buy it.' ) );
					}
				}
			break;
		}
		
		
		$result		= array();
	
		$result['actionStamp']		= $request->get( 'actionStamp', '' );
		
		return $this->jsonResponse( $result );
		
	}
	
}