<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();

        $videoGames = array_fill_callback(0, 50, fn (int $index): VideoGame => (new VideoGame)
            ->setTitle(sprintf('Jeu vidéo %d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(($index % 5) + 1)
            ->setImageName(sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
        );

        $this->addTags($videoGames, $tags);

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        // TODO : Ajouter des reviews aux vidéos

    }

    public function getDependencies(): array
    {
        return [TagFixtures::class];
    }

    private function addTags(array $videoGames, array $tags): void
    {
        @mt_srand(12);
        /** @var VideoGame $videoGame */
        foreach($videoGames as $videoGame)
        {
            $coll = $videoGame->getTags();
            $nTag = rand(1, 4);
            for ($i = 0; $i < $nTag; $i++) {
                $coll->add($tags[rand(0, count($tags) - 1)]);
            }
        }
    }
}
