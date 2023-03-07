<?php

namespace Vng\EvaCore\Observers;

use Vng\EvaCore\Interfaces\IsManagerInterface;
use Vng\EvaCore\Models\Manager;

class ManagerObserver
{
    public function creating(Manager $manager): void
    {
        /** @var IsManagerInterface $creatingUser */
        $creatingUser = request()->user();
        if ($creatingUser) {
            $creatingManager = $creatingUser->manager()->first();
            if ($creatingManager) {
                $manager->createdBy()->associate($creatingManager);
            }
        }
    }
}
