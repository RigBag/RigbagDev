<?php

namespace Proton\RigbagBundle\Controller;

use Symfony\Component\BrowserKit\Response;

use Proton\RigbagBundle\Entity\User;
use Proton\RigbagBundle\Entity\Transaction;
use Proton\RigbagBundle\Entity\TransactionPayPalDetails;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IphoneController extends \ProtonLabs_Controller {
	
	
	// CLOSE
	/*********
	 * 
	 */
	public function closeAction( Request $request ) {
		
		echo '<script>window.close();</script>';
		exit();
	}
	
	// BUY ADVERT
	/**************
	 * 
	 */
	public function buyAdvertAction( Request $request ) {
		
		
		$em = $this->getDoctrine()->getManager();
		
		$advertId = $request->get( 'advertId', null );
		$method = $request->get( 'method', null );
		$userId = $request->get( 'userId', null );
		
		$ap	= new \PayPal_AdaptivePayment();
		
		$logger = new \PayPal_AdaptivePayment_PPLoggingManager('ProtonLabs');
		$logger->info("Buy Item");
		
		$result ['success'] = 0;
		
		if ( !is_null( $advertId ) && !is_null( $method ) && !is_null( $userId )) {
			
			$user = $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );
			$advert = $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );
			
			if ($advert && $user && $advert->getState() == 'enabled' && $advert->getMode() == 'sale' && $advert->getUserId() != $userId) {
				$advert->setState( 'during_deal' );
				
				
				$transaction = new Transaction();
				
				$transaction->setFromUser( $user )->setFromUserEmail( $user->getEmail() )->setFromUserName( $user->getName() )->setToUser( $advert->getUser() )->setToUserEmail( $advert->getUser()->getEmail() )->setToUserName( $advert->getUser()->getName() )->setAdvert( $advert )->setAmount( $advert->getPrice() )->setCurrency( strtoupper( $advert->getCurrency() ) )->setDescription( 'Buy item - advert no.' . $advert->getId() )->setType( 'buy' )->setMethod( 'paypal' )->setState( 'processing' );
				
				$em->persist( $transaction );
				$em->flush();
				
				$payPalConf = $this->container->getParameter( 'paypal' );
				
				$sign = new \PPSignatureCredential( $payPalConf ['username'], $payPalConf ['password'], $payPalConf ['signature'] );
				$sign->setApplicationId( $payPalConf ['application_id'] );
				
				$receiver = array();
				$receiver [0] = new \Receiver();
				
				$receiver [0]->email = 'rigbag_1360188582_biz@protonlabs.co'; // $advert->getPaypalId();
				$receiver [0]->amount = $advert->getPrice();
				$receiver [0]->paymentType = 'GOODS';
				
				$receiverList = new \ReceiverList ( $receiver );
				
				$cancelUrl = $this->getHost( true ) . $this->generateUrl( 'payments_cancel', array (
						'method' => 'paypal',
						'type' => 'buy',
						'ti' => $transaction->getId() 
				) );
				$okUrl = $this->getHost( true ) . $this->generateUrl( 'payments_return', array (
						'method' => 'paypal',
						'type' => 'buy',
						'ti' => $transaction->getId() 
				) );
				
				$payRequest = new \PayRequest( new \RequestEnvelope( "en_US" ), 'PAY', $cancelUrl, strtoupper( $advert->getCurrency() ), $receiverList, $okUrl );
				
				$ipnUrl = $this->getHost( true ) . $this->generateUrl( 'payments_ipn' );
				
				$payRequest->feesPayer = 'EACHRECEIVER';
				$payRequest->senderEmail = '';
				$payRequest->ipnNotificationUrl = $ipnUrl;
				$payRequest->memo = 'RigBag - advert no.' . $advert->getId();
				$payRequest->trackingId = $transaction->getId();
				$payRequest->currencyCode = strtoupper( $advert->getCurrency() );
				
				$service = new \AdaptivePaymentsService();
				
				try {
					$response = $service->Pay( $payRequest, $sign );
					
					if ($response->responseEnvelope->ack == 'Success') {
						
						$ack = $response->responseEnvelope->ack;
						$payKey = $response->payKey;
						
						$token = $response->payKey;
						$payPalURL = $payPalConf['paypal_url'] . '?cmd=_ap-payment&paykey=' . $token;
						
						$transaction->setToken( $token );
						$em->flush();
						
						return $this->redirect( $payPalURL );
					} else {
						return $this->redirect( $this->generateUrl( 'iphone_close', array () ) );
					}
				} catch ( \Exception $ex ) {
					return $this->redirect( $this->generateUrl( 'iphone_close', array () ) );
				}
			}
		}
		
		
	}
}
