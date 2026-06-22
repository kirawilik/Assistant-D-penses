<?php

namespace Tests\Feature;

use App\Enums\DepenseCategorie;
use App\Models\Depense;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepensesListeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_depenses(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $recuA = Recu::factory()->create(['user_id' => $userA->id]);
        $recuB = Recu::factory()->create(['user_id' => $userB->id]);

        Depense::factory()->create(['recu_id' => $recuA->id, 'libelle' => 'Pain']);
        Depense::factory()->create(['recu_id' => $recuA->id, 'libelle' => 'Lait']);
        Depense::factory()->create(['recu_id' => $recuB->id, 'libelle' => 'Savon']);

        $response = $this->actingAs($userA)->get(route('depenses.index'));

        $response->assertStatus(200);
        $response->assertSee('Pain');
        $response->assertSee('Lait');
        $response->assertDontSee('Savon');
    }

    public function test_liste_complete_avec_eager_loading(): void
    {
        $user = User::factory()->create();

        $recu1 = Recu::factory()->create(['user_id' => $user->id]);
        $recu2 = Recu::factory()->create(['user_id' => $user->id]);

        Depense::factory()->create(['recu_id' => $recu1->id, 'libelle' => 'Pain']);
        Depense::factory()->create(['recu_id' => $recu1->id, 'libelle' => 'Lait']);
        Depense::factory()->create(['recu_id' => $recu2->id, 'libelle' => 'Savon']);

        $response = $this->actingAs($user)->get(route('depenses.index'));

        $response->assertStatus(200);
        $response->assertSee('Pain');
        $response->assertSee('Lait');
        $response->assertSee('Savon');
    }

    public function test_filtre_par_categorie_valide(): void
    {
        $user = User::factory()->create();
        $recu = Recu::factory()->create(['user_id' => $user->id]);

        Depense::factory()->create([
            'recu_id' => $recu->id,
            'libelle' => 'Pain',
            'categorie' => DepenseCategorie::ALIMENTAIRE,
        ]);
        Depense::factory()->create([
            'recu_id' => $recu->id,
            'libelle' => 'Savon',
            'categorie' => DepenseCategorie::HYGIENE,
        ]);

        $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'alimentaire']));

        $response->assertStatus(200);
        $response->assertSee('Pain');
        $response->assertDontSee('Savon');
    }

    public function test_filtre_categorie_invalide_ignoree_sans_500(): void
    {
        $user = User::factory()->create();
        $recu = Recu::factory()->create(['user_id' => $user->id]);

        Depense::factory()->create(['recu_id' => $recu->id, 'libelle' => 'Pain']);

        $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'invalide']));

        $response->assertStatus(200);
        $response->assertSee('Pain');
    }

    public function test_liste_vide_quand_aucune_depense_ne_correspond(): void
    {
        $user = User::factory()->create();
        $recu = Recu::factory()->create(['user_id' => $user->id]);

        Depense::factory()->create([
            'recu_id' => $recu->id,
            'categorie' => DepenseCategorie::BOISSONS,
        ]);

        $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'entretien']));

        $response->assertStatus(200);
        $response->assertSeeText('Aucune dépense trouvée');
    }
}