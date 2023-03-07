<?php

namespace Vng\EvaCore\Observers;

use Vng\EvaCore\Interfaces\EvaUserInterface;
use Vng\EvaCore\Interfaces\IsManagerInterface;
use Vng\EvaCore\Repositories\ManagerRepositoryInterface;

class UserObserver
{
    public function __construct(
        protected ManagerRepositoryInterface $managerRepository
    )
    {}

    public function creating(EvaUserInterface $user)
    {
        $user->assignRandomPassword();
    }

    /**
     * @param EvaUserInterface&IsManagerInterface $user
     * @return void
     */
    public function created(EvaUserInterface $user): void
    {
        $this->managerRepository->createForUser($user);
        $user->sendAccountCreationNotification();
    }

    public function deleted(IsManagerInterface $user): void
    {
        $manager = $user->getManager();
        if (!is_null($manager)) {
            $this->managerRepository->delete($manager->id);
        }
    }
}
