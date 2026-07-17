<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use App\Twig\Components\Tabs;
use LDAP\Result;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**
     * @dataProvider shouldFilterVideoGamesByTagDataProvide
     * 
     * @param int[] $tags
     * @param int $result
     * @return void
     */
    public function testShouldFilterVideoGamesByTag(array $tags, int $result): void
    {
        $this->get('/');
        $this->client->submitForm( 'Filtrer', self::generateTagFiltersSubmit($tags),'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($result, 'article.game-card');
    }

    public function testResultesHasFilterTagNoTags(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        $crawler = $this->client->getCrawler();
        $elements = $crawler->filter('.tag:contains("Game Tag 2")');
        $count = $elements->count();

        self::assertEquals(4, $count);
    }

    /**
     * @dataProvider resultesHasFilterTagsDataProvider
     * 
     * @param int[] $tagGroup
     * @return void
     */
    public function testResultesHasFilterTags(array $tagGroup): void
    {
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

    /**
     * @dataProvider notExistingTagsDataProvider
     * 
     * @param string $route
     * @param int[] $tags
     * @return void
     */
    public function testNotExistingTags(string $route, array $tags): void
    {
        $this->get($route);
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        $crawler = $this->client->getCrawler();
        $elements = $crawler->filter('.tag:contains("Game Tag 2")');
        $count = $elements->count();

        self::assertEquals(4, $count);

        $error = false;
        try {
            $this->client->submitForm('Filtrer', self::generateTagFiltersSubmit($tags), 'GET');
        } catch (\Throwable $th) {
            $error = true;
        }
        self::assertTrue($error);
        
    }

    /**
     * Summary of generateTagFiltersSubmit
     * @param int[] $tagIds
     * @return array<string, int>
     */
    private function generateTagFiltersSubmit(array $tagIds): array
    {
        $result = [];
        foreach($tagIds as $id) {
            $key = $id - 1;
            $result["filter[tags][$key]"] = $id;
        }
        return $result;
    }

    /**
     * @return array<array{
     *    tags:int[],
     *    result:int
     * }>
     */
    public static function shouldFilterVideoGamesByTagDataProvide(): array
    {
        return [
            [
                'tags' => [3],
                'result' => 9,
            ],
            [
                'tags' => [3,4],    
                'result' => 2,
            ],
            [
                'tags' => [3,6],    
                'result' => 4,
            ],
            [
                'tags' => [3,6,7],    
                'result' => 1,
            ]
        ];
    }

    /**
     * @return array<array{
     *    tagGroup:int[]
     * }>
     */
    public static function resultesHasFilterTagsDataProvider(): array
    {
        return [
            [
               'tagGroup' => [3]
            ],
            [
                'tagGroup' => [2,4]
            ],
        ];
    }

    /**
     * @return array<array{
     *    route:string,
     *    tags:int[]
     * }>
     */
    public static function notExistingTagsDataProvider() : array 
    {
        return [
            [
                'route' => '/?filter[tags][]=2333',
                'tags' => [2333]
            ]
        ];
    }
}
