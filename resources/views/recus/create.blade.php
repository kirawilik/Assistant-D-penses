<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau reçu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Nouveau reçu</h1>

        <form action="{{ route('recus.store') }}" method="POST" class="bg-white shadow rounded p-6">
            @csrf

            <label for="texte_source" class="block font-medium mb-2">Texte du reçu</label>
            <textarea name="texte_source" id="texte_source" rows="8" class="w-full border rounded px-3 py-2" placeholder="Collez le texte extrait de votre reçu..." required></textarea>

            @error('texte_source')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Soumettre</button>
                <a href="{{ route('recus.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>