<?php

// app/Traits/Loggable.php

namespace App\Traits;

use App\Models\Log;

trait Loggable
{
    /**
     * Crée un log pour une action utilisateur.
     *
     * @param int $userId
     * @param string $action
     * @param string|null $details
     * @return Log
     */
    public function createLog($userId, $action, $details = null)
    {
        return Log::create([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }
}