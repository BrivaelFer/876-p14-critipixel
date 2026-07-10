<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use App\Twig\Components\Tabs;
use LDAP\Result;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    public function testTagFiltersButtonExist(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, '#filter_tags>div');
    }

    public function testShouldFilterVideoGamesByTag(): void
    {
        $tests = [
            [
                'result' => 9,
                'tags' => [3]
            ],
            [
                'result' => 2,
                'tags' => [3,4]
            ],
            [
                'result' => 4,
                'tags' => [3,6]
            ],
            [
                'result' => 1,
                'tags' => [3,6,7]
            ]
        ];
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        foreach($tests as $test) {
            $this->get('/');
            $this->client->submitForm( 'Filtrer', self::generateTagFiltersSubmit($test['tags']),'GET');
            self::assertResponseIsSuccessful();
            self::assertSelectorCount($test['result'], 'article.game-card');
        }
    }

    public function testResultesHasFilterTags(): void
    {
        $tagGroups = [
            [3],
            [2,4],
        ];
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        $crawler = $this->client->getCrawler();
        $elements = $crawler->filter('.tag:contains("Game Tag 2")');
        $count = $elements->count();

        self::assertEquals(4, $count);

        foreach($tagGroups as $tagGroup) {
            $this->get('/');
            self::assertResponseIsSuccessful();
            $this->client->submitForm('Filtrer', self::generateTagFiltersSubmit($tagGroup), 'GET');
            self::assertResponseIsSuccessful();

            $crawler = $this->client->getCrawler();
            $cardCount = $crawler->filter('article.game-card')->count();
            foreach($tagGroup as $tagId) {
                $value = $tagId - 1;
                $count = $crawler->filter('.tag:contains("Game Tag '. $value . '")')->count();
                self::assertEquals($cardCount, $count);
            }
        }
    }

    private function generateTagFiltersSubmit(array $tagIds): array
    {
        $result = [];
        foreach($tagIds as $id) {
            $key = $id - 1;
            $result["filter[tags][$key]"] = $id;
        }
        return $result;
    }
}
