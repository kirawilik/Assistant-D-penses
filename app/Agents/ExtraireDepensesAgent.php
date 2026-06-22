<?php

namespace App\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Strict;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Groq)]
#[Strict]
class ExtraireDepensesAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'Tu es un assistant specialise dans l\'extraction de depenses a partir de texte de recus. '
            . 'Extrais chaque article avec son libelle, sa quantite, son prix unitaire et sa categorie. '
            . 'Retourne toujours le total estime et la devise. '
            . 'RESPECTE STRICTEMENT LE SCHEMA JSON fourni. '
            . 'Ne modifie PAS les cles du schema. '
            . 'Si le texte ne contient aucun article detectable, retourne une liste articles vide (tableau []). '
            . 'N\'invente JAMAIS de donnees qui ne sont pas dans le texte source.';
    }

    public function messages(): iterable
    {
        return [];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'articles' => $schema->array()->items(
                $schema->object([
                    'libellé' => $schema->string()->required(),
                    'quantité' => $schema->number()->required(),
                    'prix_unitaire' => $schema->number()->required(),
                    'catégorie' => $schema->string()->required()->enum([
                        'alimentaire', 'boissons', 'hygiène', 'entretien', 'autre',
                    ]),
                ]),
            )->required(),
            'total_estimé' => $schema->number()->required(),
            'devise' => $schema->string()->required(),
        ];
    }
}
