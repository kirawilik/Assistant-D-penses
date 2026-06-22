<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes d&eacute;penses</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Mes d&eacute;penses</h1>

        <form method="GET" action="{{ route('depenses.index') }}" class="mb-6">
            <label for="categorie" class="mr-2 font-medium">Filtrer par cat&eacute;gorie :</label>
            <select name="categorie" id="categorie" onchange="this.form.submit()" class="border rounded px-3 py-1">
                <option value="">Toutes les cat&eacute;gories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" {{ $categorie === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
            @if ($categorie)
                <a href="{{ route('depenses.index') }}" class="ml-2 text-sm text-blue-600 hover:underline">R&eacute;initialiser</a>
            @endif
        </form>

        @if ($depenses->isEmpty())
            <p class="text-gray-500">Aucune d&eacute;pense trouv&eacute;e.</p>
        @else
            <table class="w-full bg-white shadow rounded">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-2">Libell&eacute;</th>
                        <th class="p-2">Quantit&eacute;</th>
                        <th class="p-2">Prix unitaire</th>
                        <th class="p-2">Cat&eacute;gorie</th>
                        <th class="p-2">Re&ccedil;u</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($depenses as $depense)
                        <tr class="border-t">
                            <td class="p-2">{{ $depense->libelle }}</td>
                            <td class="p-2">{{ $depense->quantite }}</td>
                            <td class="p-2">{{ number_format((float) $depense->prix_unitaire, 2) }} &euro;</td>
                            <td class="p-2">{{ $depense->categorie->label() }}</td>
                            <td class="p-2">
                                <a href="{{ route('recus.show', $depense->recu) }}" class="text-blue-600 hover:underline">
                                    Re&ccedil;u #{{ $depense->recu_id }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="mt-6">
            <a href="{{ route('recus.index') }}" class="text-blue-600 hover:underline">&larr; Retour aux re&ccedil;us</a>
        </div>
    </div>
</body>
</html>