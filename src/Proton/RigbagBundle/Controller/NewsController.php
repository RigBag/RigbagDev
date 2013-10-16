<?php

namespace Proton\RigbagBundle\Controller;



use Symfony\Component\HttpFoundation\JsonResponse;

use Proton\RigbagBundle\Entity\News;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class NewsController extends \ProtonLabs_Controller
{
	
	public function listAction( Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() )
		{
			$em 		= $this->getDoctrine()->getManager();
			$result		= array();
			$engine 	= $this->container->get('templating');
			
			$news		= $em->getRepository( 'ProtonRigbagBundle:News' )->findBy( array(), array( 'add_date' => 'desc' ) );
			
			$result		= array(
					'toUpdate'	=> array( 'headerTop', 'headerBottom', 'content', 'bodyClass', 'headerExtras' ),
					'header'	=> array(
							'top'		=> $engine->render( 'ProtonRigbagBundle:News:header-top.html.twig', array( ) ),
							'bottom'	=> '',
							'extras'	=> ''
					),
					'bodyClass'	=> 'news-list',
					'content'	=> $engine->render( 'ProtonRigbagBundle:News:content-list.html.twig', array( 'news' => $news ) )
			);
			
			
			$result['actionStamp']		= $request->get( 'actionStamp', '' );
			
			return new JsonResponse( $result, 200 );
		}
		
		return $this->blackholeResponse();
	}
	
	
}