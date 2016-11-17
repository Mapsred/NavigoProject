<?php


namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\Card;

ini_set('memory_limit', '-1');

/**
 * Class LoadLocationData
 * @package AppBundle\DataFixtures\ORM
 */
class LoadLocationData implements FixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $cards = file(__DIR__."/../Data/cards.lst");
        echo count($cards)." cards to add \n";
        foreach ($cards as $key => $card) {
            $obj = new Card();
            $obj->setUuid($card);
            $manager->persist($obj);
            echo "Card ".($key + 1)."/".count($cards)." added\n";

            if (($key+1)%500 == 0) {
                echo "500 Cards flushed\n";
                $manager->flush();
            }
        }

        $manager->flush();
    }
}