<?php

namespace Proton\RigbagBundle\Controller;

use WindowsAzure\Blob\Models\CreateBlobOptions;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proton\RigbagBundle\Entity\TmpUpload;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Common\Blob;


class ImageController extends \ProtonLabs_Controller
{
	protected function setHeader( $ext, $response ) {

		switch( strtolower( $ext ) ) {
			case 'jpeg':
			case 'jpg':
				$response->headers->set( 'Content-type', 'image/jpeg' );
				break;
			case 'gif':
				$response->headers->set( 'Content-type', 'image/gif' );
				break;
			case 'png':
				$response->headers->set( 'Content-type', 'image/png' );
			break;
		}

	}

	protected function generateImage( $pathSrc, $width, $height, $zoomCrop, $pathCache, $cacheFileName, $forceZoom = false ) {

		if( !is_null( $pathSrc ) && ( !is_null( $width ) || !is_null( $height ) ) )
		{
			\ProtonLabs_Dir::makeDirectory( $pathCache );

			$image	= \PhpThumb_Factory::create( $pathSrc, array( 'jpegQuality' => 50 ) );

			if( $zoomCrop ) {
				$image->adaptiveResize( $width, $height );
			} else {
				$image->resize( $width, $height );
			}

			$image->save( $pathCache . $cacheFileName, 'JPG' );
		}

	}

	protected function readImage( $path, $response ) {
		$response->setPublic();
		$response->setContent( file_get_contents( $path ) );
		$response->setETag(md5($response->getContent()));
		$response->isNotModified( $this->getRequest() );
	}

	public function sportAction( $sportId, $width, $height, Request $request ) {

		$this->setupLocale($request);
		$response	= new Response();
		$response->prepare($request);

		$em 		= $this->getDoctrine()->getManager();
		$paths		= $this->container->getParameter( 'paths' );

		$sport		= $em->getRepository( 'ProtonRigbagBundle:Interest' )->find( $sportId );
		$src		= $paths['storage']['sport'] . $sport->getPicture();

		$zoomCrop	= $request->get( 'zc', 1 );

		if( is_null( $width ) ) {
			$width	= $height;
		} elseif( is_null( $height ) ) {
			$height	= $width;
		}

		$pathBuffor	= \ProtonLabs_Dir::generateBufforPath( $sportId );
		$pathSrc	= $paths['storage']['sport'] . $sport->getPicture();
		$tmp		= pathinfo( $sport->getPicture() );
		$pathCache	= $paths['cache']['sport'] . $pathBuffor . $sportId . '/';
		$fileName	= $width . 'x' . $height . '-' . $zoomCrop . '.jpg';

		$this->setHeader( $tmp['extension'], $response );
		$generateFile	= true;

		if( file_exists( $pathCache . $fileName ) ) {
			$cacheTime	= filemtime( $pathCache . $fileName );
			$orgTime	= filemtime( $pathSrc );

			if( $cacheTime && $orgTime && $orgTime <= $cacheTime ) {
				$generateFile = false;
			}
		}

		if( $generateFile ) {
			$this->generateImage( $pathSrc, $width, $height, $zoomCrop, $pathCache, $fileName );
		}

		$this->readImage( $pathCache . $fileName, $response );

		return $response;
	}

	public function advertAction( $imageId, $width, $height, $forceZoom, Request $request ) {

		$this->setupLocale($request);
		$response	= new Response();
		$response->prepare($request);

		$em 		= $this->getDoctrine()->getManager();
		$paths		= $this->container->getParameter( 'paths' );
		$zoomCrop	= $request->get( 'zc', 1 );

		if( is_null( $width ) ) {
			$width	= $height;
		} elseif( is_null( $height ) ) {
			$height	= $width;
		}

		$image		= $em->getRepository( 'ProtonRigbagBundle:AdvertImage' )->find( $imageId );

		$pathBuffor	= \ProtonLabs_Dir::generateBufforPath( $imageId );
		$pathSrc	= $paths['storage']['advert'] . $image->getPath();

		$pathCache	= $paths['cache']['advert'] . $pathBuffor . $imageId . '/';
		$fileName	= $width . 'x' . $height . '-' . $zoomCrop . '.jpg';

		$this->setHeader( $image->getExtension(), $response );
		$generateFile	= true;

		if( file_exists( $pathCache . $fileName ) ) {
			$cacheTime	= filemtime( $pathCache . $fileName );
			$orgTime	= filemtime( $pathSrc );

			if( $cacheTime && $orgTime && $orgTime <= $cacheTime ) {
				$generateFile = false;
			}
		}

		if( $generateFile ) {
			if( $forceZoom ) {
				$forceZoom	= true;
			} else {
				$forceZoom	= false;
			}

			$this->generateImage( $pathSrc, $width, $height, $zoomCrop, $pathCache, $fileName, $forceZoom );
		}

		$this->readImage( $pathCache . $fileName, $response );

		return $response;

	}

	public function thumbAdvertAction( $advertId, Request $request )
	{
		$this->setupLocale($request);
		$response	= new Response();
		$response->prepare($request);

		$em 		= $this->getDoctrine()->getManager();
		$paths		= $this->container->getParameter( 'paths' );

		$image		= $em->getRepository( 'ProtonRigbagBundle:AdvertImage' )->findOneBy( array( 'is_main' => '1', 'advert_id' => $advertId ) );

		if( !$image ) {
			$image		= $em->getRepository( 'ProtonRigbagBundle:AdvertImage' )->findOneBy( array( 'advert_id' => $advertId ) );
		}

		$width		= $request->get( 'w', 220 );
		$height		= $request->get( 'h', 190 );
		$width		= $request->get( 'width', $width );
		$height		= $request->get( 'height', $height );
		$zoomCrop	= $request->get( 'zc', 1 );

		if( is_null( $width ) ) {
			$width	= $height;
		} elseif( is_null( $height ) ) {
			$height	= $width;
		}

		$pathBuffor	= \ProtonLabs_Dir::generateBufforPath( $image->getId() );
		$pathSrc	= $paths['storage']['advert'] . $image->getPath();

		$pathCache	= $paths['cache']['advert'] . $pathBuffor . $image->getId() . '/';
		$fileName	= $width . 'x' . $height . '-' . $zoomCrop . '.jpg';

		$this->setHeader( $image->getExtension(), $response );
		$generateFile	= true;

		if( file_exists( $pathCache . $fileName ) ) {
			$cacheTime	= filemtime( $pathCache . $fileName );
			$orgTime	= filemtime( $pathSrc );

		if( $cacheTime && $orgTime && $orgTime <= $cacheTime ) {
				$generateFile = false;
			}
		}

		if( $generateFile ) {
			$this->generateImage( $pathSrc, $width, $height, $zoomCrop, $pathCache, $fileName );
		}

		$this->readImage( $pathCache . $fileName, $response );

		return $response;
	}


	public function thumbAction( Request $request  )
	{
		$this->setupLocale($request);
		$response	= new Response();
		$response->prepare($request);

		$src		= urldecode( $request->get( 'src', null ) );
		$width		= $request->get( 'w', null );
		$height		= $request->get( 'h', null );
		$zoomCrop	= $request->get( 'zc', 1 );

		if( is_null( $width ) ) {
			$width	= $height;
		} elseif( is_null( $height ) ) {
			$height	= $width;
		}


		if( !is_null( $src ) && ( !is_null( $width ) || !is_null( $height ) ) )
		{
			$options	= array( 'resizeUp' => true );

			$image	= \PhpThumb_Factory::create( $src, $options );

			if( $zoomCrop ) {
				$image->adaptiveResize( $width, $height );
			} else {
				$image->resize( $width, $height );
			}

			$image->show();
		}

		return $response;
	}

	public function avatarAction( $userId, $width, $height, Request $request ) {

		$this->setupLocale($request);
		$response	= new Response();
		$response->prepare($request);

		$fullPath	= null;
		$em 		= $this->getDoctrine()->getManager();
		$paths		= $this->container->getParameter( 'paths' );
		$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $userId );

		$zoomCrop	= 1;

		$pathBuffor	= \ProtonLabs_Dir::generateBufforPath( $userId );
		$pathSrc	= $paths['storage']['avatar'] . $user->getProfilePicture();
		$tmp		= pathinfo( $user->getProfilePicture() );
		$pathCache	= $paths['cache']['avatar'] . $pathBuffor . $userId . '/';
		$fileName	= $width . 'x' . $height . '-' . $zoomCrop . '.jpg';

		$this->setHeader( $tmp['extension'], $response );
		$generateFile	= true;

		if( file_exists( $pathCache . $fileName ) ) {
			$cacheTime	= filemtime( $pathCache . $fileName );
			$orgTime	= filemtime( $pathSrc );

			if( $cacheTime && $orgTime && $orgTime <= $cacheTime ) {
				$generateFile = false;
			}
		}

		if( $generateFile ) {
			$this->generateImage( $pathSrc, $width, $height, $zoomCrop, $pathCache, $fileName );
		}

		$this->readImage( $pathCache . $fileName, $response );

		return $response;
	}

	public function deleteAction( $type, Request $request )
	{
		$this->setupLocale($request);
		if( !$this->auth() ) {
			return $this->unAuthResponse();
		}

		$em 	= $this->getDoctrine()->getManager();
		$response	= new Response();
		$response->headers->set( 'Content-type', 'application/json; charset=utf-8' );
		$result	= array();
		switch( $type ) {
			case 'tmp':
				$tmp	= $em->getRepository( 'ProtonWizzkiBundle:TmpFile' )->find( $request->get( 'fileId' ) );
				if( $tmp ) {
					$em->remove( $tmp );
					$em->flush();
				}
			break;
			case 'media':
				$tmp	= $em->getRepository( 'ProtonWizzkiBundle:MediaFile' )->find( $request->get( 'fileId' ) );
				if( $tmp ) {
					$em->remove( $tmp );
					$em->flush();
				}
			break;
		}

		return $this->render( 'ProtonWizzkiBundle:Extras:default.json.twig', array( 'result' => $result ), $response );
	}

	public function uploadAction( $type, $num, Request $request )
	{
		$this->setupLocale($request);
		if( $request->isXmlHttpRequest() ) {

			$this->auth( false );
			$this->get( 'session' )->get( 'init', null );

			switch( $type )
			{
				case 'avatar':

					$em 	= $this->getDoctrine()->getManager();

					$sizes				= array( array( 36, 36 ), array( 60, 60 ), array( 100, 100 ), array( 80, 80 ), array( 40, 40 ), array( 160, 160 ), array( 50, 50 ) );
					$allowedExtensions 	= array();
					$sizeLimit 			= 10 * 1024 * 1024;
					$paths				= $this->container->getParameter( 'paths' );

					$uploader	= new \QqFile_Uploader( $allowedExtensions, $sizeLimit );

					$path		= $paths['storage']['tmp'];

					$result				= $uploader->handleUpload( $path );

					if( isset( $result['success'] ) ) {
						$confAzure			= $this->container->getParameter( 'azure' );
						$connectionString	= 'DefaultEndpointsProtocol=' . $confAzure['storage']['protocol'] . ';AccountName=' . $confAzure['storage']['account'] . ';AccountKey=' . $confAzure['storage']['primaryKey'];

						$blobProxy			= ServicesBuilder::getInstance()->createBlobService( $connectionString );

						$user		= $em->getRepository( 'ProtonRigbagBundle:User' )->find( $this->getUserId() );

						// DELETE ALL
						$oldFile	= $user->getProfilePicture();
						try {
							$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', 'org', $oldFile ) );
							foreach( $sizes as $size ) {
								$blobProxy->deleteBlob( $confAzure['storage']['container'], str_replace( '%size%', $size[0] . 'x' . $size[1], $oldFile ) );
							}
						}
						catch( ServiceException $e ) {}

						// UPLOAD NEW
						$fileTpl	= $paths['storage']['avatar'] . $user->getId() . '_' . time() . '-%size%.jpg';
						$user->setProfilePicture( $fileTpl );

						$blobOptions	= new CreateBlobOptions();
						$blobOptions->setContentType( 'image/jpeg' );

						$em->flush();

						$orgPath		= str_replace( '%size%', 'org', $fileTpl );

						$fileContentSrc	= file_get_contents( $path . $uploader->getUploadName() );
						$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'adaptiveResize' =>false), true );
						$image->setFormat( 'JPG' );
						$fileContent	= $image->getImageAsString();

						$blobProxy->createBlockBlob( $confAzure['storage']['container'], $orgPath, $fileContent, $blobOptions );

						foreach( $sizes as $size ) {

							$image 	= \PhpThumb_Factory::create( $fileContentSrc, array( 'jpegQuality' => 75, 'resizeUp' => true ), true );
							$image->adaptiveResize( $size[0], $size[1] );

							$fPath		= str_replace( '%size%', $size[0] . 'x' . $size[1], $fileTpl );
							$fPath		= str_replace( '%ext%', 'jpg', $fPath );

							$image->setFormat( 'JPG' );

							$blobProxy->createBlockBlob( $confAzure['storage']['container'], $fPath, $image->getImageAsString(), $blobOptions );
						}

						$result['cssPath']      = $this->container->getParameter( 'azure.storage.url' ) . str_replace( '%size%', '80x80', $fileTpl );

						unlink( $path . $uploader->getUploadName() );

					}

				break;
				case 'tmpadvert':

					$em 	= $this->getDoctrine()->getManager();

					$allowedExtensions 	= array();
					$sizeLimit 			= 10 * 1024 * 1024;
					$paths				= $this->container->getParameter( 'paths' );
					$forceDirectory		= true;

					$uploader	= new \QqFile_Uploader( $allowedExtensions, $sizeLimit, $forceDirectory );

					$path		= $paths['storage']['tmp'];
					$subPath	= 'advert/' . \ProtonLabs_Dir::generatePath();

					\ProtonLabs_Dir::makeDirectory( $path . $subPath );

					$result				= $uploader->handleUpload( $path . $subPath );

					if( isset( $result['success'] ) ) {

						$num			= $request->get( 'num' );

						$t				= 'advert_photo_' . $num;

						$old			= $em->getRepository( 'ProtonRigbagBundle:TmpUpload' )->findOneBy( array( 'type' => $t, 'session_key' => $this->get('session')->getId() ) );
						if( $old ) {
							$em->remove( $old );
						}

						$tmpUpload		= new TmpUpload();
						$tmpUpload->setPath( $subPath . $uploader->getUploadName() );
						$tmpUpload->setSessionKey( $this->get( 'session' )->getId() );
						$tmpUpload->setType( $t );

						$em 	= $this->getDoctrine()->getManager();

						$em->persist( $tmpUpload );
						$em->flush();

						$result['subPath']	= $subPath;
						$result['fileName'] = $uploader->getUploadName();
						$result['fullPath'] = $path . $subPath . $uploader->getUploadName();
						$result['fileId']	= $tmpUpload->getId();
						$result['imgTag']	= '<img src="' . $request->getBaseUrl() . '/image/thumb/?src=' . $path . $subPath . $uploader->getUploadName() . '&w=80" class="img-polaroid"/>';
						$result['cssPath']	= $request->getBaseUrl() . '/image/thumb/?src=' . $path . $subPath . $uploader->getUploadName() . '&w=80';
					}

				break;
			}

		}

		return new JsonResponse( $result, 200 );


	}

}