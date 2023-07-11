<?php

namespace App\Policies;

use App\Models\DebitCard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class DebitCardPolicy
 */
class DebitCardPolicy
{
    use HandlesAuthorization;

    /**
     * View Debit cards or a specific Debit Card
     *
     * @param User           $user
     * @param DebitCard|null $debitCard
     *
     * @return bool
     */
    public function view(User $user, ?DebitCard $debitCard = null): bool
    {
        if (!$debitCard) {
            return true;
        }

        return $user->is($debitCard->user);
    }

    /**
     * Create a Debit card
     *
     * @param User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * View Debit cards or a specific Debit Card
     *
     * @param User           $user
     * @param DebitCard $debitCard
     *
     * @return bool
     */
    public function update(User $user, DebitCard $debitCard): bool
    {
        return $user->is($debitCard->user);
    }

    /**
     * View Debit cards or a specific Debit Card
     *
     * @param User           $user
     * @param DebitCard $debitCard
     *
     * @return bool
     */
    public function delete(User $user, DebitCard $debitCard): bool
    {
        return $user->is($debitCard->user)
            && $debitCard->debitCardTransactions()->doesntExist();
    }
}
