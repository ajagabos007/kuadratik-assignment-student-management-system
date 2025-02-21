<?php

namespace App\Models;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Get the parent transactionable model (user).
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    
}

