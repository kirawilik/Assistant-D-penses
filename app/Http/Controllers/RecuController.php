<?php

namespace App\Http\Controllers;

use App\Jobs\ExtraireDepensesDuRecu;
use App\Models\Recu;
use App\Http\Requests\StoreRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecuController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $recus = Auth::user()->recus()->with('depenses')->latest()->get();

        return view('recus.index', compact('recus'));
    }

    public function create(): View
    {
        return view('recus.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $recu = Recu::create([
            'user_id' => Auth::id(),
            'texte_source' => $request->texte_source,
            'statut' => 'en_attente',
        ]);

        ExtraireDepensesDuRecu::dispatch($recu)->onQueue('extractions');

        return redirect()
            ->route('recus.index')
            ->with('success', 'Reçu en cours de traitement.');
    }

    public function show(Recu $recu): View
    {
        $this->authorize('view', $recu);

        $recu->load('depenses');

        return view('recus.show', compact('recu'));
    }

    public function destroy(Recu $recu): RedirectResponse
    {
        $this->authorize('delete', $recu);

        $recu->delete();

        return redirect()
            ->route('recus.index')
            ->with('success', 'Reçu supprimé avec succès.');
    }
}
