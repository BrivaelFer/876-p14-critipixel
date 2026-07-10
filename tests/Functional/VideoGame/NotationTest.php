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
    
    public function testNoteSubmitSuccess(): void
    {
        $game = $this->videoGameRepository->find(1);
        $route = '/' . $game->getSlug();
        $values = [
            'review[rating]' => 2,
            'review[comment]' => 'Test submit 1'
        ];

        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $this->client->submitForm('Poster', $values, 'POST');
        self::assertResponseRedirects($route, 302);
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form');

        $game = $this->videoGameRepository->find(1);
        self::assertTrue($this->reviewInGame(
            $game, 
            $values['review[rating]'], 
            $values['review[comment]']
        ));
        
    }

    public function testNoteSubmitFaile(): void
    {
        $tests = [
            [
                'game' => 4,
                'values' => [
                    'review[rating]' => 9,
                    'review[comment]' => 'Test submit 2',
                    'urlParams' => '?review[rating]=9'
                ],
            ]
        ];

        foreach($tests as $test)
        {
            $game = $this->videoGameRepository->find($test['game']);
            $route = '/' . $game->getSlug();
            $values =  $test['values'];
            $this->get($route);
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('form');

            $this->get($route. $values['urlParams']);
            self::assertSelectorExists('form');
        }
        
    }

    public function testUnConnect()
    {
        $this->get('/auth/logout');
        $game = $this->videoGameRepository->find(5);
        $route = '/' . $game->getSlug();
        $values = [
            'review[rating]' => 2,
            'review[comment]' => 'Test unconnect submit'
        ];

        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form');

        $this->client->request('POST', $route, $values);
        self::assertSelectorNotExists('form');

        $game = $this->videoGameRepository->find(5);
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
}