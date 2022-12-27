<?php

namespace Takshak\Wallet\Traits;

use Takshak\Wallet\Models\Wallet;
use Takshak\Wallet\Service\Wallet as ServiceWallet;

trait HasWallet
{
    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'wallettable');
    }

    public function primaryWallet()
    {
        return $this->morphOne(Wallet::class, 'wallettable')->where('is_primary', true);
    }

    public function wallet()
    {
        if (!$this->primaryWallet) {
            return $this->createWallet();
        }
        return new ServiceWallet($this->primaryWallet);
    }

    public function getWallet($name = 'default')
    {
        $wallet = $this->wallets->where('name', $name)->first();
        return new ServiceWallet($wallet);
    }

    public function hasWallet(bool $primary = true): bool
    {
        return $this->wallets->where('is_primary', $primary)->count() ? true : false;
    }

    public function createWallet($name = 'default', $balance = 0, $is_primary = true, $remarks = '')
    {
        $wallet = $this->wallets()->create([
            'name'  =>  $name,
            'is_primary'  =>  $is_primary,
            'balance'  =>  $balance,
            'remarks'  =>  $remarks,
        ]);
        return new ServiceWallet($wallet);
    }
}
