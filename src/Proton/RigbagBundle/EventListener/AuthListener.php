<?php
namespace Proton\RigbagBundle\EventListener;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthListener
{


	public function onKernelController(FilterControllerEvent $event)
	{
		
		$controller = $event->getController();
		
// 		var_dump( $controller );
		$class 	= 'ProtonLabs_Controller';
		$class2	= 'Proton\RigbagBundle\Controller\DefaultController';
		$class3	= 'Proton\RigbagBundle\Controller\MainController';
		$class4	= 'Proton\RigbagBundle\Controller\AdvertController';
		$class5	= 'Proton\RigbagBundle\Controller\QaController';
		$class6	= 'Proton\RigbagBundle\Controller\ImageController';
		$class7	= 'Proton\RigbagBundle\Controller\UserController';
		$class8	= 'Proton\RigbagBundle\Controller\SandboxController';
		$class9	= 'Proton\RigbagBundle\Controller\ApiController';
		$class10	= 'Proton\RigbagBundle\Controller\NewsController';
		$class11	= 'Proton\RigbagBundle\Controller\PaymentController';
		$class12	= 'Proton\RigbagBundle\Controller\TestController';
		$class13	= 'Proton\RigbagBundle\Controller\IphoneController';
		
		
		if (	$controller[0] instanceof $class && 
				!(	$controller[0] instanceof $class2 || 
					$controller[0] instanceof $class3 || 
					$controller[0] instanceof $class4 || 
					$controller[0] instanceof $class5 || 
					$controller[0] instanceof $class6 ||
					$controller[0] instanceof $class7 ||
					$controller[0] instanceof $class8 ||
					$controller[0] instanceof $class9 ||
					$controller[0] instanceof $class10 ||
					$controller[0] instanceof $class11 ||
					$controller[0] instanceof $class12 ||
					$controller[0] instanceof $class13)
			) {
				if( !$controller[0]->isLoged() ) {
					
					
					$url = $controller[0]->generateUrl( 'login' );
					
					header('Location:' . $url );
					exit();
				}
		}
		
	}
}