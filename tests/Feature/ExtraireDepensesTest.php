<?php

namespace Tests\Feature;

use App\Agents\ExtraireDepensesAgent;
use App\Enums\RecuStatus;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraireDepensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_extraction_with_multiple_articles(): void
    {
        ExtraireDepensesAgent::fake([
            [
                'articles' => [
                    ['libellé' => 'Pain', 'quantité' => 2, 'prix_unitaire' => 1.50, 'catégorie' => 'alimentaire'],
                    ['libellé' => 'Lait', 'quantité' => 1, 'prix_unitaire' => 3.00, 'catégorie' => 'boissons'],
                    ['libellé' => 'Savon', 'quantité' => 1, 'prix_unitaire' => 4.00, 'catégorie' => 'hygiène'],
                ],
                'total_estimé' => 10.00,
                'devise' => 'EUR',
            ],
        ]);

        $user = User::factory()->create();
        $recu = Recu::factory()->create([
            'user_id' => $user->id,
            'texte_source' => 'Pain 2€, Lait 3€, Savon 4€',
            'statut' => RecuStatus::EN_ATTENTE,
        ]);

        (new \App\Jobs\ExtraireDepensesDuRecu($recu))->handle(app(ExtraireDepensesAgent::class));

        $recu->refresh();

        $this->assertEquals(RecuStatus::TRAITE, $recu->statut);
        $this->assertNotNull($recu->payload_ia);
        $this->assertCount(3, $recu->depenses);

        $this->assertEquals('Pain', $recu->depenses[0]->libelle);
        $this->assertEquals(2, $recu->depenses[0]->quantite);
        $this->assertEquals(1.50, (float) $recu->depenses[0]->prix_unitaire);
        $this->assertEquals('alimentaire', $recu->depenses[0]->categorie->value);
    }

    public function test_extraction_with_zero_detected_articles(): void
    {
        ExtraireDepensesAgent::fake([
            [
                'articles' => [],
                'total_estimé' => 0,
                'devise' => 'EUR',
            ],
        ]);

        $user = User::factory()->create();
        $recu = Recu::factory()->create([
            'user_id' => $user->id,
            'texte_source' => 'Bonjour',
            'statut' => RecuStatus::EN_ATTENTE,
        ]);

        (new \App\Jobs\ExtraireDepensesDuRecu($recu))->handle(app(ExtraireDepensesAgent::class));

        $recu->refresh();

        $this->assertEquals(RecuStatus::TRAITE, $recu->statut);
        $this->assertNotNull($recu->payload_ia);
        $this->assertCount(0, $recu->depenses);
    }

    public function test_extraction_failure_by_provider_exception(): void
    {
        ExtraireDepensesAgent::fake(function () {
            throw new \RuntimeException('API Groq indisponible');
        });

        $user = User::factory()->create();
        $recu = Recu::factory()->create([
            'user_id' => $user->id,
            'texte_source' => 'Pain 2€',
            'statut' => RecuStatus::EN_ATTENTE,
        ]);

        (new \App\Jobs\ExtraireDepensesDuRecu($recu))->handle(app(ExtraireDepensesAgent::class));

        $recu->refresh();

        $this->assertEquals(RecuStatus::ECHOUE, $recu->statut);
        $this->assertNotNull($recu->error_message);
        $this->assertStringContainsString('API Groq indisponible', $recu->error_message);
        $this->assertCount(0, $recu->depenses);
    }

    public function test_extraction_with_invalid_schema_treated_as_no_articles(): void
    {
        ExtraireDepensesAgent::fake([
            ['invalid_payload' => 'pas conforme'],
        ]);

        $user = User::factory()->create();
        $recu = Recu::factory()->create([
            'user_id' => $user->id,
            'texte_source' => 'Pain 2€',
            'statut' => RecuStatus::EN_ATTENTE,
        ]);

        (new \App\Jobs\ExtraireDepensesDuRecu($recu))->handle(app(ExtraireDepensesAgent::class));

        $recu->refresh();

        $this->assertEquals(RecuStatus::TRAITE, $recu->statut);
        $this->assertNull($recu->error_message);
        $this->assertCount(0, $recu->depenses);
    }
}
