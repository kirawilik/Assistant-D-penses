<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu #{{ $recu->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Reçu #{{ $recu->id }}</h1>

        <div class="bg-white shadow rounded p-6 mb-6">
            <h2 class="font-semibold mb-2">Texte source</h2>
            <p class="whitespace-pre-wrap">{{ $recu->texte_source }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 mb-6">
            <h2 class="font-semibold mb-2">Statut</h2>
            <p>{{ $recu->statut->label() }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 mb-6">
            <h2 class="font-semibold mb-2">Dépenses extraites</h2>

            @if ($recu->depenses->isEmpty())
                <p class="text-gray-500">Aucune dépense extraite.</p>
            @else
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 text-left">
                            <th class="p-2">Libellé</th>
                            <th class="p-2">Quantité</th>
                            <th class="p-2">Prix unitaire</th>
                            <th class="p-2">Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recu->depenses as $depense)
                            <tr class="border-t">
                                <td class="p-2">{{ $depense->libelle }}</td>
                                <td class="p-2">{{ $depense->quantite }}</td>
                                <td class="p-2">{{ number_format((float) $depense->prix_unitaire, 2) }} &euro;</td>
                                <td class="p-2">{{ $depense->categorie->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <a href="{{ route('recus.index') }}" class="text-blue-600 hover:underline">&larr; Retour à la liste</a>
    </div>
</body>
</html>