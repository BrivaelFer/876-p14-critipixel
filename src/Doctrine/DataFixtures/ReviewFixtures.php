<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();
        $reviews = [];

        @mt_srand(15);
        foreach ($videoGames as $videoGame) {
            $coll = $videoGame->getReviews();
            $nReview = rand(0, 5);
            for ($i = 0; $i < $nReview; ++$i) {
                $review = (new Review())
                    ->setUser($users[rand(0, count($users) - 1)])
                    ->setVideoGame($videoGame)
                    ->setRating(rand(1, 5))
                ;
                if (1 === rand(0, 1)) {
                    $review->setComment($this->faker->paragraphs(1, true));
                }
                $reviews[] = $review;
                $coll->add($review);
            }
            $this->calculateAverageRating->calculateAverage($videoGame);
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
        }

        array_walk($videoGames, [$manager, 'persist']);
        array_walk($reviews, [$manager, 'persist']);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VideoGameFixtures::class, UserFixtures::class];
    }
}
