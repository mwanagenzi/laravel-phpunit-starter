<?php

namespace Controllers;

use App\Models\Strategy;
use Illuminate\Http\Response;
use Tests\TestCase;

class StrategyControllerTests extends TestCase
{
    //index returns data in valid format
    public function testIndexReturnsDataInValidFormat()
    {
        $this->json('get', 'api/strategy')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'tenure',
                        'yield',
                        'relief',
                        'investments' => [
                            '*' => [
                                'id',
                                'user_id',
                                'strategy_id',
                                'successful',
                                'amount',
                                'returns',
                                'created_at'
                            ]
                        ],
                        'created_at',
                    ]
                ]
            ]);
    }

    //shown correctly
    public function testStrategyIsShownCorrectly()
    {
        $strategy = Strategy::create(Strategy::factory()->create()->getAttributes());

        $this->json('get', "api/strategy/$strategy->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'data' => [
                    'id' => $strategy->id,
                    'type' => $strategy->type,
                    'tenure' => $strategy->tenure,
                    'investments' => $strategy->investments,
                    'yield' => round($strategy->yield, 2, PHP_ROUND_HALF_UP),
                    'relief' => round($strategy->relief, 2, PHP_ROUND_HALF_UP),
                    'created_at' => (string)$strategy->created_at
                ]
            ]);
    }

    public function testShowMissingStrategy()
    {
        $this->json('get', "api/strategy/0")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['error']);
    }

    //storedcorrectly

    public function testStrategyIsStoredSuccessfully()
    {
        $strategy = Strategy::factory()->make()->getAttributes();

        $this->json('post', 'api/strategy', $strategy)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'tenure',
                    'relief',
                ]
            ]);
        $this->assertDatabaseHas('strategies', $strategy);

    }

    //updatedcorrectly

    public function testStrategyIsUpdatedSuccessfully()
    {

        $strategy = Strategy::create(Strategy::factory()->create()->getAttributes());
        $payload = Strategy::factory()->make()->getAttributes();

        $this->json('put', "api/strategy/$strategy->id", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $strategy->id,
                    'tenure' => $payload['tenure'],
                    'type' => $payload['type'],
                    'yield' => $payload['yield'],
                    'relief' => $payload['relief'],
                ]
            ]);
    }

    public function testUpdateMissingStrategy()
    {
        $payload = Strategy::factory()->create()->getAttributes();
        $this->json('put', "api/strategy/0", $payload)
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['error']);
    }

    //deletedcorrectly
    public function testStrategyIsDestroyedSuccessfully()
    {
        $strategyAttributes = Strategy::factory()->make()->getAttributes();
        $strategy = Strategy::create($strategyAttributes);

        $this->json('delete', "api/strategy/$strategy->id")
            ->assertStatus(Response::HTTP_NO_CONTENT)
            ->assertNoContent();

        $this->assertDatabaseMissing('strategies', $strategyAttributes);
    }

    public function testDestroyMissingStrategy()
    {
        $this->json('delete', "api/strategy/0")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['error']);
    }
}