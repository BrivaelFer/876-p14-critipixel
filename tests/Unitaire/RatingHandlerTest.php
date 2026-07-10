<?php

namespace App\Tests\Unitaire;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use Override;
use PHPUnit\Framework\TestCase;

class RatingHandlerTest extends TestCase
{
    private RatingHandler $ratingHandler;

    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    public function testCalculateAverageNoReview(): void
    {
        $vg = new VideoGame();

        $this->ratingHandler->calculateAverage($vg);

        $this->assertNull($vg->getAverageRating());
    }

    public function testCalculateAverage(): void
    {
        $tests = [
            [
                'ratings' => [2, 5],
                'result' => 4,
            ],
            [
                'ratings' => [1, 1, 1],
                'result' => 1,
            ],
            [
                'ratings' => [1, 5],
                'result' => 3,
            ],
            [
                'ratings' => [2, 2, 5, 5],
                'result' => 4,
            ],
            [
                'ratings' => [1, 2, 3, 4, 5],
                'result' => 3,
            ],
        ];

        foreach ($tests as $key => $test) {
            $vg = new VideoGame();

            $reviews = $vg->getReviews();
            foreach ($test['ratings'] as $rating) {
                $review = (new Review())->setRating($rating);
                $reviews->add($review);
            }

            $this->ratingHandler->calculateAverage($vg);

            $this->assertSame($test['result'], $vg->getAverageRating(), 'faile test ' . $key);
        }
    }

    public function testCountRatingsPerValueNoReview(): void
    {
        $vg = new VideoGame();

        $this->ratingHandler->countRatingsPerValue($vg);

        $numberOfRatingsPerValue = $vg->getNumberOfRatingsPerValue();
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfOne());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfTwo());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfThree());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFour());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFive());
    }

    public function testCountRatingsPerValueClear(): void
    {
        $test = [1, 3, 3, 2, 4, 1, 5, 5];

        $vg = new VideoGame();

        $numberOfRatingsPerValue = $vg->getNumberOfRatingsPerValue();

        foreach($test as $rating) {
            $this->incriseRatingCount($numberOfRatingsPerValue, $rating);
        }

        $this->ratingHandler->countRatingsPerValue($vg);

        
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfOne());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfTwo());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfThree());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFour());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFive());
        
    }

    public function testCountRatingsPerValue(): void
    {
        $tests = [
            [
                'results' => [
                    1 => 2,
                    2 => 4,
                    3 => 1,
                    4 => 3,
                    5 => 1,
                ],
                'ratings' => [1,1, 2,2,2,2, 3, 4,4,4, 5]
            ]
        ];
        foreach($tests as $test) {
            $vg = new VideoGame();

            $reviews = $vg->getReviews();
            
            foreach($test['ratings'] as $rating) {
                $review = (new Review())->setRating($rating);
                $reviews->add($review);
            }

            $this->ratingHandler->countRatingsPerValue($vg);

            $numberOfRatingsPerValue = $vg->getNumberOfRatingsPerValue();
            foreach($test['results'] as $rating => $count) {
                $this->assertSame($count, $this->getRatingCount($numberOfRatingsPerValue, $rating));
            }
        }
        
    }

    private function incriseRatingCount(NumberOfRatingPerValue $numberOfRatingPerValue, int $rating): void
    {
        match ($rating) {
            1 => $numberOfRatingPerValue->increaseOne(),
            2 => $numberOfRatingPerValue->increaseTwo(),
            3 => $numberOfRatingPerValue->increaseThree(),
            4 => $numberOfRatingPerValue->increaseFour(),
            default => $numberOfRatingPerValue->increaseFive(),
        };
    }

    private function getRatingCount(NumberOfRatingPerValue $numberOfRatingPerValue, int $rating): int
    {
        return match ($rating) {
            1 => $numberOfRatingPerValue->getNumberOfOne(),
            2 => $numberOfRatingPerValue->getNumberOfTwo(),
            3 => $numberOfRatingPerValue->getNumberOfThree(),
            4 => $numberOfRatingPerValue->getNumberOfFour(),
            default => $numberOfRatingPerValue->getNumberOfFive(),
        };
    }
}
