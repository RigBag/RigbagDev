<?php
namespace Proton\RigbagBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Proton\RigbagBundle\Entity\News;

class TransactionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rigbag:transactions');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em         		= $this->getContainer()->get('doctrine')->getEntityManager('default');
     	$interval			= 300;
        $transactions		= $em->getRepository( 'ProtonRigbagBundle:Transaction' )->findBy( array( 'state' => 'processing' ) );
        
        foreach( $transactions as $transaction ) {
        	if( $transaction->getCreatedAt()->getTimestamp() < ( time() - $interval ) ) {
        		$transaction->setState( 'failed' );
        		$advert	= $transaction->getAdvert();
        		if( $advert && $advert->getState() == 'during_deal' ) {
        			$advert->setState( 'enabled' );
        		}
        	}
        }
        
        $em->flush();
    }
}





