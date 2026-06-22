<?php

namespace App\Policies;

use App\Models\Depense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
}
