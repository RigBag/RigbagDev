<?php
namespace Proton\RigbagBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersUpdateCommand extends ContainerAwareCommand {
	
	
	protected function configure()
	{
		parent::configure();
	
		$this->setName('rigbag:users:update');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$em 	= $this->getContainer()->get('doctrine')->getEntityManager( 'default' );
		
		$qb		= $em->getRepository( 'ProtonRigbagBundle:User' )->createQueryBuilder( '' );
		
		$output->writeln( 's' );
	}
}