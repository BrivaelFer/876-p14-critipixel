<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Doctrine\Repository\VideoGameRepository;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;
use Doctrine\ORM\EntityRepository;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\DataProvider;

final class NotationTest extends FunctionalTestCase
{
    private EntityRepository $videoGameRepository;
    private KernelBrowser $noLogClien;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->login();
        $this->videoGameRepository = $this->getEntityManager()->getRepository(VideoGame::class);
    }
    /**
     * @dataProvider noteSubmitSuccessDataProvider
     */
    public function testNoteSubmitSuccess(int $gameId, array $values): void
    {
        $game = $this->videoGameRepository->find($gameId);
        $route = '/' . $game->getSlug();

        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $this->client->submitForm('Poster', $values, 'POST');
        self::assertResponseRedirects($route, 302);
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form');

        $game = $this->videoGameRepository->find($gameId);
        self::assertTrue($this->reviewInGame(
            $game, 
            $values['review[rating]'], 
            $values['review[comment]']
        ));
        
    }

    /**
     * @dataProvider noteSubmitFaileDataProvider
     */
    public function testNoteSubmitFaile(int $gameId, string $urlParams): void
    {
        $game = $this->videoGameRepository->find($gameId);
        $route = '/' . $game->getSlug();
        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $this->get($route. $urlParams);
        self::assertSelectorExists('form');
    }

    /**
     * @dataProvider unConnectDataProvider
     */
    public function testUnConnect(int $gameId, array $values)
    {
        $game = $this->videoGameRepository->find($gameId);
        $route = '/' . $game->getSlug();

        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $this->get('/auth/logout');
       
        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form');

        $this->client->request('POST', $route, $values);
        self::assertSelectorNotExists('form');

        $game = $this->videoGameRepository->find($gameId);
         self::assertNotTrue($this->reviewInGame(
            $game, 
            $values['review[rating]'], 
            $values['review[comment]']
        ));
    }

    private function reviewInGame(VideoGame $game, int $note, string $comment): bool
    {
        /** @var Review */
        foreach($game->getReviews() as $review) {
            if($review->getRating() === $note && $review->getComment() === $comment) return true;
        }
        return false;
    }

    public static function unConnectDataProvider(): array
    {
        return [
            [
                'gameId' => 5,
                'values' => [
                    'review[rating]' => 2,
                    'review[comment]' => 'Test unconnect submit'
                ]
            ]
        ];
    }

    public static function noteSubmitFaileDataProvider(): array 
    {
        return [
            [
                'gameId' => 4,
                'urlParams' => '?review[rating]=9'
            ]
        ];
    }

    public static function noteSubmitSuccessDataProvider(): array
    {
        return [
            [
                'gameId' => 1,
                'values' => [
                    'review[rating]' => 2,
                    'review[comment]' => 'Test submit 1'
                ]
            ]
        ];
    }
}