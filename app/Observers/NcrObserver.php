<?php

namespace App\Observers;

use App\Models\Ncr;
use App\Models\AuditLog;

class NcrObserver
{
    /**
     * Handle the Ncr "created" event.
     */
    public function created(Ncr $ncr): void
    {
        //
    }

    /**
     * Handle the Ncr "updated" event.
     */
  public function updated(Ncr $ncr)
{
    AuditLog::create([
        'module' => 'NCR',
        'module_id' => $ncr->id,
        'action' => 'updated',
        'old_data' => json_encode($ncr->getOriginal()),
        'new_data' => json_encode($ncr->getAttributes()),
        'user_id' => auth()->id() ?? 1
    ]);
}

    /**
     * Handle the Ncr "deleted" event.
     */
    public function deleted(Ncr $ncr): void
    {
        //
    }

    /**
     * Handle the Ncr "restored" event.
     */
    public function restored(Ncr $ncr): void
    {
        //
    }

    /**
     * Handle the Ncr "force deleted" event.
     */
    public function forceDeleted(Ncr $ncr): void
    {
        //
    }
}
