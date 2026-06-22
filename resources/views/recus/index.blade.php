<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes reçus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Mes reçus</h1>

        @if (session('success'))
            <p class="mb-4 text-green-600 font-medium">{{ session('success') }}</p>
        @endif

        <a href="{{ route('recus.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nouveau reçu</a>

        @if ($recus->isEmpty())
            <p class="text-gray-500">Aucun reçu pour le moment.</p>
        @else
            <table class="w-full bg-white shadow rounded">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-2">Texte source</th>
                        <th class="p-2">Statut</th>
                        <th class="p-2">Dépenses</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recus as $recu)
                        <tr class="border-t">
                            <td class="p-2">{{ Str::limit($recu->texte_source, 60) }}</td>
                            <td class="p-2">{{ $recu->statut->label() }}</td>
                            <td class="p-2">{{ $recu->depenses->count() }}</td>
                            <td class="p-2 flex gap-2">
                                <a href="{{ route('recus.show', $recu) }}" class="text-blue-600 hover:underline">Voir</a>
                                <form action="{{ route('recus.destroy', $recu) }}" method="POST" onsubmit="return confirm('Supprimer ce reçu ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="mt-6">
            <a href="{{ route('depenses.index') }}" class="text-blue-600 hover:underline">&rarr; Voir toutes mes dépenses</a>
        </div>
    </div>
</body>
</html>