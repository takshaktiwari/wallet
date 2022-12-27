<?php

namespace Takshak\Wallet\Traits;

use Takshak\Wallet\Models\WalletTransaction;

trait HasWalletTransaction
{
    public function transactions()
    {
        return $this->morphMany(WalletTransaction::class, 'transactional');
    }
}
