<?php

namespace UserBundle\Command;

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
    }

    /**
     * @param OutputInterface $output
     */
    protected function import(OutputInterface $output)
    {
        $batchSize = 25;
        $key = 1;
        $data = file(__DIR__."/../DataFixtures/Data/cards.lst");
        $size = count($data);
        $manager = $this->getContainer()->get("doctrine")->getManager();

        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach ($data as $card) {
            $obj = $manager->getRepository("UserBundle:Card")->findOneBy(['uuid' => $card]);
            if ($obj) {
                $key++;
                if (($key % $batchSize) === 0) {
                    $progress->advance($batchSize);
                }

                continue;
            }
            $obj = new Card();
            $obj->setUuid($card);
            $manager->persist($obj);

            if (($key % $batchSize) === 0) {
                $manager->flush();
                $manager->clear();

                $progress->advance($batchSize);
                $now = new \DateTime();
                $output->writeln(' of users imported ... | '.$now->format('d-m-Y G:i:s'));
            }
            $key++;
        }

        // Flushing and clear data on queue
        $manager->flush();
        $manager->clear();

        // Ending the progress bar process
        $progress->finish();
    }

}
