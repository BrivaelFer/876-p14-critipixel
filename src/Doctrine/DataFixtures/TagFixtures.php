<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = array_fill_callback(0, 10, fn (int $index): Tag => (new Tag)
            ->setName('Game Tag ' . $index)
        );

        array_walk($tags, [$manager, 'persist']);

        $manager->flush();
    }
}
