<?php
namespace Proton\RigbagBundle\Controller;

set_time_limit( 600 );

use WindowsAzure\Blob\Models\CreateBlobOptions;

use Symfony\Component\BrowserKit\Response;

use Proton\RigbagBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;

class TestController extends \ProtonLabs_Controller
{
	public function blobAction() {

		var_dump( $this->msBlobFileExist( 'http://portalvhds128m9nlx3zp7g.blob.core.windows.net/media/advert/10-13581160381-org.jpg' ) );

		exit();
	}

	public function indexAction() {

		$conf				= $this->container->getParameter( 'azure' );

		$connectionString	= 'DefaultEndpointsProtocol=' . $conf['storage']['protocol'] . ';AccountName=' . $conf['storage']['account'] . ';AccountKey=' . $conf['storage']['primaryKey'];


		$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );
		$blob_list = $blobProxy->listBlobs( $conf['storage']['container'] );
		$blobs = $blob_list->getBlobs();


		echo 'BLOBS LIST: <br/>';
		foreach( $blobs as $blob ) {
			//$blobProxy->deleteBlob( $conf['storage']['container'], $blob->getName() );
		}
		echo '<br/><br/>';

// 		$blobOptions	= new CreateBlobOptions();
// 		$blobOptions->setBlobContentType( 'text/plain' );
// 		$blobProxy->createBlockBlob( $conf['storage']['container'], 'tt/34/test-' . rand( 1, 10 ) . '.txt', 'To dziala?', $blobOptions );




		exit();
	}


	public function updateAction() {
		exit();
		$em 		= $this->getDoctrine()->getManager();

		$paths				= $this->container->getParameter( 'paths' );
		$confAzure			= $this->container->getParameter( 'azure' );
		$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

		$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

		if( $this->getRequest()->get( 'clear', 0 ) ) {
			$blob_list = $blobProxy->listBlobs( $confAzure['storage']['container'] );
			$blobs = $blob_list->getBlobs();

			foreach( $blobs as $blob ) {
				$blobProxy->deleteBlob( $confAzure['storage']['container'], $blob->getName() );
			}
		}


		$blobOptions	= new CreateBlobOptions();
		$blobOptions->setContentType( 'image/jpeg' );


		$users		= $em->getRepository( 'ProtonRigbagBundle:User' )->findAll();
		$sizes				= array( 'org', array( 60, 60 ), array( 100, 100 ), array( 80, 80 ), array( 40, 40 ), array( 160, 160 ), array( 50, 50 ) );



		foreach( $users as $user ) {
			$fileTpl	= $paths['storage']['avatar'] . $user->getId() . '_' . time() . '-%size%.jpg';
			$user->setProfilePicture( $fileTpl );

			if( !file_exists( '../storage/' . $paths['storage']['avatar'] . $user->getProfilePicture() ) || !strlen( $user->getProfilePicture() ) )  {
				continue;
			}


			$fileContentSrc	= file_get_contents( '../storage/' . $paths['storage']['avatar'] . $user->getProfilePicture() );


			foreach( $sizes as $size ) {
				$s			= ( !is_array( $size ) ? $size : implode( 'x', $size ) );
				$orgPath	= str_replace( '%size%', $s, $fileTpl );
				$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 90, 'adaptiveResize' =>false), true );
				if( is_array( $size ) ) {
					$image->adaptiveResize( $size[0], $size[1] );
				}
				$image->setFormat( 'JPG' );
				$fileContent	= $image->getImageAsString();


				try {
					$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );
				} catch( ServiceException $e ) {}
			}
		}

		$sizes				= array( 'org', array( 220, 190 ), array( 440, 380 ), array( 80, 69 ), array( 50, 50 ), array( 60, 60 ), array( 173, 149 ), array( 60, 51 ), array( 340, 294 ) );
		$adverts	= $em->getRepository( 'ProtonRigbagBundle:Advert' )->findAll();

		foreach( $adverts as $advert ) {
			$images	= $advert->getImages();

			foreach( $images as $k => $img ) {

				$fileTpl		= $paths['storage']['advert'] . $advert->getUserId() . '-' . time() . $k . '-%size%.jpg';
				$pathRead		= $paths['storage']['advert'] . $img->getPath();

				$img->setPath( $fileTpl );

				if( !file_exists( '../storage/' . $pathRead ) && !strlen( $img->getPath() )  ) continue;

				$fileContentSrc	= file_get_contents( '../storage/' . $pathRead );


				foreach( $sizes as $size ) {
					$s			= ( !is_array( $size ) ? $size : implode( 'x', $size ) );

					$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 90, 'adaptiveResize' =>false), true );
					if( is_array( $size ) ) {
						$image->adaptiveResize( $size[0], $size[1] );
					}
					$image->setFormat( 'JPG' );
					$orgPath		= str_replace( '%size%', $s, $fileTpl );

					$fileContent	= $image->getImageAsString();
					try {
						$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );
					} catch( ServiceException $e ) {}
				}
			}
		}

		$em->flush();

		exit();
	}
}