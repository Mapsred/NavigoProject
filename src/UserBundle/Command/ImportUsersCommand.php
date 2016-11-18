<?php

namespace UserBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\Card;
use UserBundle\Repository\CardRepository;

class ImportUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('import:user_file')->setDescription('Import user file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : '.$now->format('d-m-Y G:i:s').' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($output);

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : '.$now->format('d-m-Y G:i:s').' ---</comment>');

        return null;
    }

    /**
     * @param OutputInterface $output
     */
    protected function import(OutputInterface $output)
    {
        $fileName = $this->getContainer()->get("kernel")->getCacheDir()."/users.lst";
        if (is_file($fileName)) {
            $data = file($fileName);
        } else {
            $data = file("http://cdn.mindgame.ovh/navigo/users.lst");
            file_put_contents($fileName, $data);
        }
        $size = count($data);
        /** @var Connection $connexion */
        $connexion = $this->getContainer()->get("doctrine")->getConnection();
        $manager = $this->getContainer()->get("doctrine")->getManager();

        $connexion->getConfiguration()->setSQLLogger(null);

        $progress = new ProgressBar($output, $size);
        $progress->start();
        $base = "UPDATE `card` SET `firstname`='%s', `lastname`='%s' WHERE `id` = %s";

        foreach ($data as $user) {
            $user = str_replace("\n", "", $user);
            $user = explode(" ", $user);
            $obj = $manager->getRepository("UserBundle:Card")
                ->findOneBy(['firstname' => $user[1], 'lastname' => $user[0]]);
            if ($obj) {
                $progress->advance();

                continue;
            }
            /** @var CardRepository $repo */
            $repo = $manager->getRepository("UserBundle:Card");
            $card = $repo->findOneWithNoUser();
            $request = sprintf($base, $user[1], $user[0],$card->getId());
            $connexion->executeQuery($request);
            $progress->advance();
        }
        // Ending the progress bar process
        $progress->finish();
    }

}
