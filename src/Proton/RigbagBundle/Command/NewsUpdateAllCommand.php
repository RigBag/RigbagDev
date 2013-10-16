<?php
namespace Proton\RigbagBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Proton\RigbagBundle\Entity\News;

class NewsUpdateAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rigbag:news:update:all')
             ->addArgument('source', InputArgument::REQUIRED, 'Source');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $SOURCES    = array( 'twitter' );
        $em         = $this->getContainer()->get('doctrine')->getEntityManager('default');
        $source     = $input->getArgument('source');
        $userRepo   = $em->getRepository('ProtonRigbagBundle:User');
        if(!in_array($source, $SOURCES))
        {
            $output->writeln('<error>Unrecognized source</error>');
            return;
        }

        if ($source == 'twitter')
        {
            $users = $userRepo->createQueryBuilder('n')
                ->where('n.twitter_token IS NOT NULL')
                ->getQuery()
                ->getResult();

            //FIXME add limit and queue
            $insertedNews = array();
            foreach ($users as $user)
            {
                $output->writeln('<info>Processing user: #'.$user->getId().': '.$user->getName().'</info>');
                if(!$user->getTwitterId() || !$user->getTwitterToken())
                {
                    continue;
                }
                $twToken  = $user->getTwitterToken();
                if (!isset($twToken['oauth_token']) && !isset($twToken['oauth_token_secret'])) {
                        continue;
                }
                $config   = $this->getContainer()->getParameter( 'social' );
                $tmhOAuth = new \TmhOAuth_Main(array(
                    'consumer_key'          => $config['twitter']['consumer_key'],
                    'consumer_secret'       => $config['twitter']['consumer_secret'],
                    'user_token'            => $twToken['oauth_token'],
                    'user_secret'           => $twToken['oauth_token_secret'],
                    'curl_ssl_verifypeer'   => false
                ));
                $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/favorites/list.json'), array(
                    'screen_name' => 'RigBag',
                    'count' => 30,
                ));

                if($code == 200)
                {
                    $c  = 0;    
                    $timeline = json_decode($tmhOAuth->response['response'], true);

                    foreach($timeline as $position) 
                    {
                        $tmp = $em->getRepository('ProtonRigbagBundle:News')->findOneBy(array('tw_id' => $position['id']));
                        if(!$tmp && !in_array($position['id'], $insertedNews)) 
                        {
                            $c++;
                            $news = new News();
                            $news->setAddDate(new \DateTime('@' . strtotime($position['created_at'])))
                                ->setTwId($position['id'])
                                ->setContent($position['text'])
                                ->setTwUserId($position['user']['id'])
                                ->setTwUserName($position['user']['name'])
                                ->setTwUserScreenName($position['user']['screen_name'])
                                ->setTwUserUrl((isset($position['user']['url']) ? $position['user']['url'] : ''))
                                ->setTwUserPicture((isset($position['user']['profile_image_url']) ? $position['user']['profile_image_url'] : ''));
                            $em->persist($news);
                            $insertedNews[] = $position['id'];
                        }
                    }
                    $output->writeln('<info>New: ' . $c . '</info>');
                }
                else 
                {
                    $output->writeln('<error>Twitter error: ' . $code . '</error>');
                }
            }
            $em->flush();
        }
    }
}





