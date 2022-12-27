<?php

namespace Takshak\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get all of the transactions for the Wallet
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }


    /* public function deposit(float $amount, $title = null, $remarks = null, $model = null)
    {
        $this->balance = $this->balance + $amount;
        $this->save();
        $this->newTransaction(
            $amount,
            $title ? $title : 'Amount deposited',
            $remarks,
            $model
        );
        return $this;
    }

    public function add(float $amount, $title = null, $remarks = null, $model = null)
    {
        return $this->deposit(
            $amount,
            $title ? $title : 'Amount deposited',
            $remarks,
            $model
        );
    }

    public function withdraw(float $amount, bool $force = false, $title = null, $remarks = null, $model = null)
    {
        if ($amount < 0) {
            throw new \ErrorException('Withdrawal amount should be greater than 0');
        }
        if (!$force && $this->balance < $amount) {
            throw new \ErrorException('Insufficient Balance');
        }

        $this->balance = $this->balance - $amount;
        $this->save();
        $this->newTransaction(
            $amount,
            $title ? $title : 'Amount Withdrawal',
            $remarks,
            $model
        );
        return $this;
    }

    public function forceWithdraw(float $amount, $title = null, $remarks = null, $model = null)
    {
        return $this->withdraw(
            $amount,
            true,
            $title ? $title : 'Amount Withdrawal',
            $remarks,
            $model
        );
    }

    public function newTransaction(float $amount, $title = null, $remarks = null, $model = null)
    {
        $transactionalData = [
            'wallet_id' =>  $this->id,
            'amount'    =>  $amount,
            'title'     =>  $title,
            'remarks'   =>  $remarks,
        ];

        if ($model && is_subclass_of($model, '\Illuminate\Database\Eloquent\Model')) {
            $model->transactions()->create($transactionalData);
        } else {
            WalletTransaction::create($transactionalData);
        }
    } */
}
