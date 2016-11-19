<?php
/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 2016-11-19
 * Time: 00:24
 */

namespace UserBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Repository\CardRepository;

class ImportUsersFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Connection $connexion */
        $connexion = $this->getContainer()->get("doctrine")->getConnection();
        $connexion->getConfiguration()->setSQLLogger(null);
        if (!is_dir($fixtureDir = $this->getContainer()->get("kernel")->getCacheDir()."/fixture_data")) {
            mkdir($fixtureDir);
        }
        $tempFile = $this->getContainer()->get("kernel")->getCacheDir()."/fixture_data/users.sql";
        if (!is_file($tempFile)) {
            $fileName = $this->getContainer()->get("kernel")->getCacheDir()."/fixture_data/users.lst";
            if (is_file($fileName)) {
                $data = file($fileName);
            } else {
                $data = file("http://cdn.mindgame.ovh/navigo/users.lst");
                file_put_contents($fileName, $data);
            }

            $manager = $this->getContainer()->get("doctrine")->getManager();

            $base = "UPDATE `card` SET `firstname`='%s', `lastname`='%s' WHERE `id` = %s";
            $request = [];

            foreach ($data as $key => $user) {
                $user = str_replace("\n", "", $user);
                $user = explode(" ", $user);
                $obj = $manager->getRepository("UserBundle:Card")
                    ->findOneBy(['firstname' => $user[1], 'lastname' => $user[0]]);
                if ($obj) {
                    continue;
                }
                $request [] = sprintf($base, $user[1], $user[0], ($key + 1));
                $now = new \DateTime();
                echo(($key + 1).' of '.count($data).' cards imported ... | '.$now->format('d-m-Y G:i:s')."\n");
            }

            file_put_contents($tempFile, implode(";\n", $request));
            echo "File $tempFile created \n";
        }else {
            echo "File $tempFile already created, using it \n";
        }
        $now = new \DateTime();
        echo("Start : ".$now->format('d-m-Y G:i:s')." ---\n");
        $file = file_get_contents($tempFile);
        $connexion->exec($file);
        $now = new \DateTime();
        echo("End : ".$now->format('d-m-Y G:i:s')." ---\n");

    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}