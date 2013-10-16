<?php

namespace Proton\RigbagBundle\Controller;

use Symfony\Component\BrowserKit\Response;

use Proton\RigbagBundle\Entity\User;
use Proton\RigbagBundle\Entity\Transaction;
use Proton\RigbagBundle\Entity\TransactionPayPalDetails;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends \ProtonLabs_Controller
{

	public function ipnAction( Request $request ) {
		$this->setupLocale($request);
		$responseAction	= new Response();

		$payPalConf		= $this->container->getParameter( 'paypal' );

		$ipnListener	= new \ProtonLabs_PayPal_IPNListener();
		$validator		= new \ProtonLabs_PayPal_IPNListener_Validator( $payPalConf['paypal_url'] );

		$message		= $ipnListener->getLastMessage();

		$ap	= new \PayPal_AdaptivePayment();

		
		if( !is_null( $message ) ) {

			$data	= $message->getData();

			$logger = null;
			$validator->verify( $message, $logger );

			if( $message->isValid() )
			{

				$em 		= $this->getDoctrine()->getManager();
				$transId	= $message->getTransactionId();

				if( is_array( $transId ) ) {
					$transId	= array_pop( array_reverse( $transId ) );
				}


				if( !is_null( $transId ) ) {
					$transaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findOneBy( array( 'txn_id' => $transId ) );
				}
				else {
					$transId		= $message->getTrackingId();
					$transaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->find( $transId );
				}


				if( $transaction && $transaction->getState() == 'processing' ) {

					$ppDetails	= new TransactionPayPalDetails();

					$txnParentId	= $message->getParentTransactionId();
					if( $txnParentId ) {
						$pTransaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findOneBy( array( 'txn_id' => $txnParentId ) );

						if( $pTransaction ) {
							$ppDetails->setParentId( $pTransaction->getId() );
						}
					}


					$ppDetails->setTransaction( $transaction )
									->setType( $message->getTransactionType() )
									->setVerifySign( $message->getVerifySign() )
									->setNotifyVersion( $message->getNotifyVersion() )
									->setReceiptId( $message->getReceiptId() )
									->setReceiverId( $message->getReceiverId() )
									->setReceiverEmail( $message->getReceiverEmail() )
									->setReceiverName( $message->getBusiness() )
									->setResend( $message->getResend() )
									->setSenderId( $message->getPayerId() )
									->setSenderEmail( $message->getPayerEmail() )
									->setSenderBuisness( $message->getPayerBusinessName() )
									->setSenderName( $message->getFirstName() . ' ' . $message->getLastName() )
									->setSenderPhone( $message->getContactPhone() )
									->setSenderAddressStatus( $message->getAddressStatus() )
									->setSenderCountryCode( $message->getAddressCountryCode() )
									->setSenderCountry( $message->getAddressCountry() )
									->setSenderCity( $message->getAddressCity() )
									->setSenderStreet( $message->getAddressStreet() )
									->setSenderZip( $message->getAddressZip() )
									->setAuthorizationStatus( $message->getAuthStatus() )
									->setExchangeRate( $message->getExchangeRate() )
									->setPaymentStatus( $message->getPaymentStatus() )
									->setPaymentType( $message->getPaymentType() )
									->setPendingReason( $message->getPendingReason() )
									->setMcCurrency( $message->getMCCurrency() )
									->setMcGross( $message->getMCGross() )
									->setMemo( $message->getMemo() )
									->setTrackingId( $message->getTrackingId() )
									->setReasoneCode( $message->getReasonCode() );

					$em->persist( $ppDetails );




					switch( $transaction->getType() ) {
						case 'subscription':

							if( strtolower( $message->getPaymentStatus() ) == 'completed' ) {

								$user	= $transaction->getFromUser();

								$expDate	= $user->getExpiredAt();

								if( $expDate ) {
									$expDate	= strtotime( $expDate->format( 'Y-m-d H:i:s' ) );
								} else {
									$expDate	= strtotime( date( 'Y-m-d H:i:s' ) );
								}

								$expDate	= $expDate + 365 * 24 * 60 * 60;

								$user->setExpiredAt( new \DateTime( '@' . $expDate ) );
								$user->setAccountType( 'annual' );

								$transaction->setState( 'completed' );
							}
							elseif ( 	strtolower( $message->getPaymentStatus() ) == 'denied' ||
										strtolower( $message->getPaymentStatus() ) == 'expired' ||
										strtolower( $message->getPaymentStatus() ) == 'failed' ||
										strtolower( $message->getPaymentStatus() ) == 'voided' ) {

								$transaction->setState( 'failed' );
							}
							else {
								$transaction->setState( 'processing' );
							}

						break;

						case 'advert':

							$advert	= $transaction->getAdvert();

							if( strtolower( $message->getPaymentStatus() ) == 'completed' ) {

								if( $advert->getState() != 'closed' )
								{
									$advert->setState( 'enabled' );
								}
								$transaction->setState( 'completed' );


								// LATE ACTIONS
								$actions	= $em->getRepository( 'ProtonRigbagBundle:LastAction' )->findBy( array( 'advert_id' => $advert->getId() ) );
								$config		= $this->container->getParameter( 'social' );


								foreach( $actions as $action ) {
									$action->run( $config );
								}

							}
							elseif ( 	strtolower( $message->getPaymentStatus() ) == 'denied' ||
									strtolower( $message->getPaymentStatus() ) == 'expired' ||
									strtolower( $message->getPaymentStatus() ) == 'failed' ||
									strtolower( $message->getPaymentStatus() ) == 'voided' ) {

								$transaction->setState( 'failed' );

								if( $advert->getState() != 'closed' )
								{
									$advert->setState( 'waiting_for_payment' );
								}
							}
							else {
								$transaction->setState( 'processing' );

								if( $advert->getState() != 'closed' )
								{
									$advert->setState( 'waiting_for_payment' );
								}
							}

						break;

						case 'buy':


							if( strtolower( $message->getPaymentStatus() ) == 'completed' ) {

								$transaction->getAdvert()->setState( 'sold' );
								$transaction->setState( 'completed' );

							}
							elseif ( 	strtolower( $message->getPaymentStatus() ) == 'denied' ||
									strtolower( $message->getPaymentStatus() ) == 'expired' ||
									strtolower( $message->getPaymentStatus() ) == 'failed' ||
									strtolower( $message->getPaymentStatus() ) == 'voided' ) {

								$transaction->setState( 'failed' );

								if( $transaction->getAdvert()->getState() != 'closed' && $transaction->getAdvert()->getState() != 'sold' )
								{
									$transaction->getAdvert()->setState( 'enabled' );
								}
							}
							else {
								$transaction->setState( 'processing' );

								if( $transaction->getAdvert()->getState() != 'closed' && $transaction->getAdvert()->getState() != 'sold' )
								{
									$transaction->getAdvert()->setState( 'during_deal' );
								}
							}

						break;
					}


					$em->flush();

				}
			}
		}


		exit();
		return $responseAction;
	}

	public function buyAction( $method, $advertId, Request $request ) {
		
		$this->setupLocale($request);
		$responseAction	= new Response();	

		$ap		= new \PayPal_AdaptivePayment();
		$mobile	= $request->get( 'mobile', 0 );
		
		if( $mobile ) {
			$userId = $request->get( 'userId', null );
		}
		else {
			$userId = $this->getUserId();
		}
		
		$logger = new \PayPal_AdaptivePayment_PPLoggingManager('ProtonLabs');
		$logger->info("Buy Item");

		$responseAction	= new Response();
		$em 			= $this->getDoctrine()->getManager();

		$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

		if( $advert && $advert->getState() == 'enabled' && $advert->getMode() == 'sale' )
		{
			$advert->setState( 'during_deal' );

			$user			= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

			$transaction		= new Transaction();

			$transaction->setFromUser( $user )
							->setFromUserEmail( $user->getEmail() )
							->setFromUserName( $user->getName() )
							->setToUser( $advert->getUser() )
							->setToUserEmail( $advert->getUser()->getEmail() )
							->setToUserName( $advert->getUser()->getName() )
							->setAdvert( $advert )
							->setAmount( $advert->getPrice() )
							->setCurrency( strtoupper( $advert->getCurrency() ) )
							->setDescription( 'Buy item - advert no.' . $advert->getId() )
							->setType( 'buy' )
							->setMethod( 'paypal' )
							->setState( 'processing' );

			$em->persist( $transaction );
			$em->flush();

			$payPalConf		= $this->container->getParameter( 'paypal' );

			$sign	= new \PPSignatureCredential( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );
			$sign->setApplicationId( $payPalConf['application_id'] );

			

			$receiver					= array();
			$receiver[0]				= new \Receiver();

			$receiver[0]->email 		= $advert->getPaypalId();
			$receiver[0]->amount 		= $advert->getPrice();
			$receiver[0]->paymentType	= 'GOODS';

			$receiverList 	= new \ReceiverList($receiver);

			$cancelUrl 		= $this->getHost( true ) . $this->generateUrl( 'payments_cancel', array( 'method' => 'paypal', 'type' => 'buy', 'ti' => $transaction->getId() ) ) ;
			$okUrl 			= $this->getHost( true ) . $this->generateUrl( 'payments_return', array( 'method' => 'paypal', 'type' => 'buy', 'ti' => $transaction->getId() ) );

			if( $mobile ) {
				$cancelUrl .= '&mobile=1';
			}
			else {
				$okUrl .= '&mobile=1';
			}
			
			$payRequest 	= new \PayRequest(
										new \RequestEnvelope("en_US"),
										'PAY',
										$cancelUrl,
										strtoupper( $advert->getCurrency() ),
										$receiverList,
										$okUrl
									);

			$ipnUrl = $this->getHost( true ) . $this->generateUrl( 'payments_ipn' );


			$payRequest->feesPayer				= 'EACHRECEIVER';
			$payRequest->senderEmail			= '';
			$payRequest->ipnNotificationUrl		= $ipnUrl;
			$payRequest->memo					= 'RigBag - advert no.' . $advert->getId();
			$payRequest->trackingId				= $transaction->getId();
			$payRequest->currencyCode			= strtoupper( $advert->getCurrency() );

			$service = new \AdaptivePaymentsService();

			try {
				$response = $service->Pay($payRequest, $sign);

				if( $response->responseEnvelope->ack == 'Success' ) {

					$ack	= $response->responseEnvelope->ack;
					$payKey = $response->payKey;

					$token = $response->payKey;
					
					if( $mobile ) {
						$payPalURL = 'https://www.paypal.com/webapps/adaptivepayment/flow/pay?paykey=' . $token . '&expType=mini';
					}
					else {
						$payPalURL = $payPalConf['paypal_url'] . '?cmd=_ap-payment&paykey=' . $token;
					}

					$transaction->setToken( $token );
					$em->flush();

					return $this->redirect( $payPalURL );
				} else {
// 					var_dump( $payPalConf );
// 					var_dump( $sign );
					//var_dump( $response );
					//exit();
					return $this->redirect( $this->generateUrl( 'start', array() ) . '#/adverts/list/sale/' );
				}

			} catch(\Exception $ex) {
				var_dump( $ex );
				exit;
			}
		}
		return $this->redirect( $this->generateUrl( 'start', array() ) . '#/adverts/list/sale/' );
	}

	public function advertAction( $method, $advertId, Request $request ) {

		$this->setupLocale($request);

		$responseAction	= new Response();
		$em 			= $this->getDoctrine()->getManager();

		$advert	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->find( $advertId );

		if( !$advert || $advert->getState() != 'waiting_for_payment' ) {

			if( !$advert ) {
				return $this->redirect( $this->generateUrl( 'start', array() ) . '#/adverts/list/sale/' );
			} else {
				return $this->redirect( $this->generateUrl( 'start', array() ) . '#/adverts/list/' . $advert->getMode() . '/' );
			}
		}
		else {

			$payPalConf		= $this->container->getParameter( 'paypal' );
			$prices			= $this->container->getParameter( 'prices' );

			$payPal			= new \ProtonLabs_PayPal( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );

			$payPal->setEndPoint( $payPalConf['end_point'] )
						->setPayPalUrl( $payPalConf['paypal_url'] );


			$transaction	= new Transaction();

			$transaction->setFromUser( $advert->getUser() )
						->setFromUserEmail( $advert->getUser()->getEmail() )
						->setFromUserName( $advert->getUser()->getName() )
						->setAdvert( $advert )
						->setAmount( $prices['advert_add'] )
						->setCurrency( 'EUR' )
						->setDescription( 'RigBag Advert no.' . $advert->getId() )
						->setType( 'advert' )
						->setMethod( 'paypal' )
						->setState( 'processing' );

			$em->persist( $transaction );
			$em->flush();

			$payPal->setExpressCheckout( array(
					'orderParams'	=>	array(
							'PAYMENTREQUEST_0_AMT'				=> $prices['advert_add'],
							'PAYMENTREQUEST_0_SHIPPINGAMT'		=> '0',
							'PAYMENTREQUEST_0_CURRENCYCODE'		=> 'EUR',
							'PAYMENTREQUEST_0_ITEMAMT'			=> $prices['advert_add']
					),
					'item'			=> 	array(
							'L_PAYMENTREQUEST_0_NAME0'			=> 'RigBag Advert',
							'L_PAYMENTREQUEST_0_DESC0'			=> 'Advert no.' . $advert->getId(),
							'L_PAYMENTREQUEST_0_AMT0'			=> $prices['advert_add'],
							'L_PAYMENTREQUEST_0_QTY0'			=> 1
					),
					'requestParams'	=> array(
							'RETURNURL'							=> $this->getHost( true ) . $this->generateUrl( 'payments_return', array( 'method' => 'paypal', 'type' => 'advert', 'ti' => $transaction->getId() ) ),
							'CANCELURL'							=> $this->getHost( true ) . $this->generateUrl( 'payments_cancel', array( 'method' => 'paypal', 'type' => 'advert', 'ti' => $transaction->getId() ) )
					)
			));


			$token		= $payPal->getToken();

			$em->flush();

			if( $payPal->hasErrors() ) {
				return $responseAction;
			}

			return $this->redirect( $payPal->getPaymentUrl() );

		}
	}

	public function subscriptionAction( $type, Request $request ) {

		$this->setupLocale($request);
		//ticket #14 - free in beta
		return $this->subscribeFree($request);

		$responseAction	= new Response();

		$em 		= $this->getDoctrine()->getManager();

		$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

		if( $user ) {

			$payPalConf		= $this->container->getParameter( 'paypal' );
			$prices			= $this->container->getParameter( 'prices' );

			$transaction	= new Transaction();

			$transaction->setFromUser( $user )
						->setFromUserEmail( $user->getEmail() )
						->setFromUserName( $user->getName() )
						->setAmount( $prices['subscription_annual'] )
						->setCurrency( 'EUR' )
						->setDescription( 'Annual subscription' )
						->setType( 'subscription' )
						->setMethod( 'paypal' )
						->setState( 'processing' );

			$em->persist( $transaction );
			$em->flush();


			$payPal			= new \ProtonLabs_PayPal( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );

			$payPal->setEndPoint( $payPalConf['end_point'] )
					->setPayPalUrl( $payPalConf['paypal_url'] );

			$transactionId	= $payPal->setExpressCheckout( array(
				'orderParams'	=>	array(
					'PAYMENTREQUEST_0_AMT'				=> $prices['subscription_annual'],
					'PAYMENTREQUEST_0_SHIPPINGAMT'		=> '0',
					'PAYMENTREQUEST_0_CURRENCYCODE'		=> 'EUR',
					'PAYMENTREQUEST_0_ITEMAMT'			=> $prices['subscription_annual'],
				),
				'item'			=> 	array(
					'L_PAYMENTREQUEST_0_NAME0'			=> 'RigBag Subscription',
					'L_PAYMENTREQUEST_0_DESC0'			=> 'Annual subscription',
					'L_PAYMENTREQUEST_0_AMT0'			=> $prices['subscription_annual'],
					'L_PAYMENTREQUEST_0_QTY0'			=> 1
				),
				'requestParams'	=> array(
					'RETURNURL'							=> $this->getHost( true ) . $this->generateUrl( 'payments_return', array( 'method' => 'paypal', 'type' => 'subscription', 'ti' => $transaction->getId() ) ),
					'CANCELURL'							=> $this->getHost( true ) . $this->generateUrl( 'payments_cancel', array( 'method' => 'paypal', 'type' => 'subscription', 'ti' => $transaction->getId() ) )
				)
			));

			$token		= $payPal->getToken();


			if( $payPal->hasErrors() ) {
				return $responseAction;
			}

			return $this->redirect( $payPal->getPaymentUrl() );

		}

	}

	public function subscribeFree(Request $request)
	{
		$this->setupLocale($request);
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('ProtonRigbagBundle:User')->find($this->getUserId());
		if (!$user){
			return;
		}

		//create dummy transaction
		$transaction = new Transaction();
		$transaction->setFromUser($user)
					->setFromUserEmail($user->getEmail())
					->setFromUserName($user->getName())
					->setAmount(0)
					->setCurrency('EUR')
					->setDescription('Annual subscription (free in beta')
					->setType('subscription')
					->setMethod('paypal')
					->setState('completed');
		$em->persist($transaction);

		//set user account type
		$user->setAccountType('annual');
		$nowPlusYear = new \DateTime();
		$nowPlusYear->setTimestamp(time() + 31540000);
		$user->setExpiredAt($nowPlusYear);

		//save
		$em->flush();

		//login user
		$this->loginUser(array(
			'id'			=> $user->getId(),
			'description'	=> $user->getName()
		));

		//message + redirect
		$this->get('session')->set('flashMessage', array(
			'title' => '',
			'content' => 'CONGRATULATIONS!!! You have just subscribed RigBag.',
			'type' => 'info')
		);
		$sufixUrl = '/settings/subscription/';
		return $this->redirect($this->generateUrl('start', array()) . '#' . $sufixUrl);
	}

	public function cancelAction( $type, $method, Request $request ) {

		$this->setupLocale($request);
		if( !$this->auth() ) {
			return $this->unAuthResponse();
		}

		$em 			= $this->getDoctrine()->getManager();

		$transaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->find( $request->get( 'ti', null ) );

		switch( $type ) {

			case 'buy':
				$transaction->getAdvert()->setState( 'enabled' );
				$sufixUrl = '/adverts/view/' . $transaction->getAdvert()->getHash() . '/';
			break;
			case 'advert':
				$sufixUrl = '/adverts/view/' . $transaction->getAdvert()->getHash() . '/';
			break;
			case 'subscription':
				$sufixUrl = '/settings/subscription/';
			break;
		}

		$transaction->setState( 'canceled' );

		$em->flush();

		return $this->redirect( $this->generateUrl( 'start', array() ) . '#' . $sufixUrl );
	}

	public function returnAction( $type, $method, Request $request ) {

		$this->setupLocale($request);
		$responseAction	= new Response();

		$em 		= $this->getDoctrine()->getManager();

		switch( $type ) {

			// ADVERT
			case 'advert':

				$payPalConf		= $this->container->getParameter( 'paypal' );
				$transactionId	= $request->get( 'ti', null );
				$payerId		= $request->get( 'PayerID', null );
				$token			= $request->get( 'token', null );


				if( !is_null( $transactionId ) && !is_null( $payerId ) && !is_null( $token ) ) {

					$prices			= $this->container->getParameter( 'prices' );

					$payPal			= new \ProtonLabs_PayPal( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );

					$payPal->setEndPoint( $payPalConf['end_point'] )
							->setPayPalUrl( $payPalConf['paypal_url'] );

					$payPal->setToken( $token );

					$details		= $payPal->getExpressCheckoutDetails();

					$requestParams = array(
							'TOKEN' 						=> $token,
							'PAYMENTACTION' 				=> 'Sale',
							'PAYERID' 						=> $payerId,
							'PAYMENTREQUEST_0_AMT' 			=> $prices['advert_add'],
							'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
							'PAYMENTREQUEST_0_NOTIFYURL'	=> $this->getHost( true ) . $this->generateUrl( 'payments_ipn' )
					);

					$txn_id = $payPal->doExpressCheckout( $requestParams );

					$transaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->find( $transactionId );

					$transaction->setTxnId( $txn_id );



					$this->get('session')->set( 'flashMessage', array( 'title' => '', 'content' => 'CONGRATULATIONS!!! You have just published advert.', 'type' => 'info' ) );
					$advert		= $transaction->getAdvert();

					$advert->setState( 'enabled' );

					$sufixUrl = '/adverts/view/' . $advert->getHash() . '/';

					$em->flush();
				}

			break;

			// SUBSCRIPTION
			case 'subscription':

				$payPalConf		= $this->container->getParameter( 'paypal' );
				$transactionId	= $request->get( 'ti', null );
				$payerId		= $request->get( 'PayerID', null );
				$token			= $request->get( 'token', null );


				if( !is_null( $transactionId ) && !is_null( $payerId ) && !is_null( $token ) ) {

					$prices			= $this->container->getParameter( 'prices' );

					$payPal			= new \ProtonLabs_PayPal( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );

					$payPal->setEndPoint( $payPalConf['end_point'] )
							->setPayPalUrl( $payPalConf['paypal_url'] );

					$payPal->setToken( $token );

					$details		= $payPal->getExpressCheckoutDetails();

					$requestParams = array(
							'TOKEN' 						=> $token,
							'PAYMENTACTION' 				=> 'Sale',
							'PAYERID' 						=> $payerId,
							'PAYMENTREQUEST_0_AMT' 			=> $prices['subscription_annual'],
							'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
							'PAYMENTREQUEST_0_NOTIFYURL'	=> $this->getHost( true ) . $this->generateUrl( 'payments_ipn' )
					);

					$txn_id = $payPal->doExpressCheckout( $requestParams );

					$transaction	= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->find( $transactionId );

					$transaction->setTxnId( $txn_id );

					$em->flush();

					$this->get('session')->set( 'flashMessage', array( 'title' => '', 'content' => 'CONGRATULATIONS!!! You have just subscribed RigBag.', 'type' => 'info' ) );
				}

				$sufixUrl = '/settings/subscription/';

			break;

			// BUY
			case 'buy':

				$ap	= new \PayPal_AdaptivePayment();

				$transaction		= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->find( $request->get( 'ti', null ) );

				if( $transaction )
				{

					$em->flush();

					$payPalConf		= $this->container->getParameter( 'paypal' );

					$sign	= new \PPSignatureCredential( $payPalConf['username'], $payPalConf['password'], $payPalConf['signature'] );
					$sign->setApplicationId( $payPalConf['application_id'] );

					$paymentDetailsRequest 	= new \PaymentDetailsRequest(
							new \RequestEnvelope("en_US")
					);

					$this->get('session')->set( 'flashMessage', array( 'title' => '', 'content' => 'CONGRATULATIONS!!! You have just bought this item.', 'type' => 'info' ) );

					$paymentDetailsRequest->payKey	= $transaction->getToken();

					$service = new \AdaptivePaymentsService();

					try {
						$response = $service->PaymentDetails( $paymentDetailsRequest, $sign );

						$transaction->setTxnId( $response->paymentInfoList->paymentInfo[0]->transactionId );

						$em->flush();
					}
					catch (\Exception $e) {

					}

					$advert		= $transaction->getAdvert();

					$sufixUrl		= '/adverts/view/' . $advert->getHash() . '/';
				}
			break;
		}




		return $this->redirect( $this->generateUrl( 'start', array() ) . '#' . $sufixUrl );
	}



}