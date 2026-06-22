<?php

namespace App\Http\Controllers;

use App\Enums\DepenseCategorie;
use App\Models\Depense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DepenseController extends Controller
{
    public function index(Request $request): View
    {
        $categorie = $request->query('categorie');

        if ($categorie && !DepenseCategorie::tryFrom($categorie)) {
            $categorie = null;
        }

        $depenses = Depense::query()
            ->whereHas('recu', fn ($q) => $q->where('user_id', Auth::id()))
            ->when($categorie, fn ($q) => $q->where('categorie', $categorie))
            ->with('recu')
            ->latest()
            ->get();

        $categories = DepenseCategorie::cases();

        return view('depenses.index', compact('depenses', 'categories', 'categorie'));
    }
}
