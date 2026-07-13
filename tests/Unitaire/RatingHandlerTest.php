<?php

namespace App\Tests\Unitaire;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RatingHandlerTest extends TestCase
{
    private RatingHandler $ratingHandler;

    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    #region Tests
    public function testCalculateAverageNoReview(): void
    {
        $vg = new VideoGame();

        $this->ratingHandler->calculateAverage($vg);

        $this->assertNull($vg->getAverageRating());
    }

    /**
     * @dataProvider calculateAverageDataProvider
     */
    public function testCalculateAverage(array $ratings, int $result): void
    {
        $vg = new VideoGame();

        $reviews = $vg->getReviews();
        foreach ($ratings as $rating) {
            $review = (new Review())->setRating($rating);
            $reviews->add($review);
        }

        $this->ratingHandler->calculateAverage($vg);

        $this->assertSame($result, $vg->getAverageRating());
        
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

    /**
     * @dataProvider countRatingsPerValueClearDataProvider
     */
    public function testCountRatingsPerValueClear(array $ratings): void
    {
        $vg = new VideoGame();

        $numberOfRatingsPerValue = $vg->getNumberOfRatingsPerValue();

        foreach($ratings as $rating) {
            $this->incriseRatingCount($numberOfRatingsPerValue, $rating);
        }

        $this->ratingHandler->countRatingsPerValue($vg);

        
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfOne());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfTwo());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfThree());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFour());
        $this->assertSame(0, $numberOfRatingsPerValue->getNumberOfFive());
    }

    private static function countRatingsPerValueClearDataProvider() : array 
    {
        return [
            [
                'ratings' => [1, 3, 3, 2, 4, 1, 5, 5]
            ]
        ];
    }

    /**
     * @dataProvider countRatingsPerValueDataProvider
     */
    public function testCountRatingsPerValue(array $ratings, array $results): void
    {
        $vg = new VideoGame();

        $reviews = $vg->getReviews();
        
        foreach($ratings as $rating) {
            $review = (new Review())->setRating($rating);
            $reviews->add($review);
        }

        $this->ratingHandler->countRatingsPerValue($vg);

        $numberOfRatingsPerValue = $vg->getNumberOfRatingsPerValue();
        foreach($results as $rating => $count) {
            $this->assertSame($count, $this->getRatingCount($numberOfRatingsPerValue, $rating));
        }
    }
    #region private
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
    #endregion
    #endregion

    #region Data provider
    public static function calculateAverageDataProvider(): array
    {
        return [
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
    }
    private static function countRatingsPerValueDataProvider() : array 
    {
        return [
            [
                'ratings' => [1,1, 2,2,2,2, 3, 4,4,4, 5],
                'results' => [
                    1 => 2,
                    2 => 4,
                    3 => 1,
                    4 => 3,
                    5 => 1,
                ]
            ]
        ];
    }
    #endregion
}
