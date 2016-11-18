<?php

namespace UserBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\Card;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('import:file')->setDescription('Import file');
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
        $batchSize = 10;
        $key = 1;
        $fileName = $this->getContainer()->get("kernel")->getCacheDir()."/cards.lst";
        if (is_file($fileName)) {
            $data = file($fileName);
        } else {
            $data = file("http://cdn.mindgame.ovh/navigo/cards.lst");
            file_put_contents($fileName, $data);
        }
        $size = count($data);
        /** @var Connection $connexion */
        $connexion = $this->getContainer()->get("doctrine")->getConnection();
        $manager = $this->getContainer()->get("doctrine")->getManager();

        $connexion->getConfiguration()->setSQLLogger(null);

        $progress = new ProgressBar($output, $size);
        $progress->start();
        $base = "INSERT INTO `card` (`uuid`) VALUES ";
        $request = [];

        foreach ($data as $card) {
            $card = str_replace("\n", "", $card);
            $obj = $manager->getRepository("UserBundle:Card")->findOneBy(['uuid' => $card]);
            if ($obj) {
                $key++;
                if (($key % $batchSize) === 0) {
                    $progress->advance($batchSize);
                }

                continue;
            }
            $request []= sprintf("('%s')", $card);

            if (($key % $batchSize) === 0) {
                $query = $base.implode(",\n", $request);
                $connexion->executeQuery($query);
                $request = [];
                $progress->advance($batchSize);
                $now = new \DateTime();
                $output->writeln(' of cards imported ... | '.$now->format('d-m-Y G:i:s'));
            }
            $key++;
        }
        // Ending the progress bar process
        $progress->finish();
    }

}
