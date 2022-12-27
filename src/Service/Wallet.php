<?php

namespace Takshak\Wallet\Service;

use Takshak\Wallet\Models\Wallet as ModelsWallet;
use Takshak\Wallet\Models\WalletTransaction;

class Wallet
{
    public $wallet;
    public $balance;

    public $referenceModel;
    public $amount;
    public $title;
    public $remarks;
    public $force = false;

    public function __construct(ModelsWallet $wallet)
    {
        if (!$wallet instanceof ModelsWallet) {
            throw new \ErrorException('Provide a valid wallet');
        }

        $this->wallet = $wallet;
        $this->balance = $this->wallet->balance;
    }

    public function referenceModel($referenceModel)
    {
        if (!is_subclass_of($referenceModel, '\Illuminate\Database\Eloquent\Model')) {
            throw new \ErrorException('Not a valid reference / transactional model provided');
        }

        $this->referenceModel = $referenceModel;
        return $this;
    }

    public function amount(float $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function title(string|null $title)
    {
        $this->title = $title;
        return $this;
    }

    public function remarks(string|null $remarks)
    {
        $this->remarks = $remarks;
        return $this;
    }

    public function force(bool $force)
    {
        $this->force = $force;
        return $this;
    }

    public function deposit(float $amount = null, string $title = '')
    {
        $this->amount = $amount ? $amount : $this->amount;
        $this->wallet->balance = $this->wallet->balance + $this->amount;
        $this->wallet->save();
        $this->balance = $this->wallet->balance;

        $this->title = $title ? $title : $this->title;
        if (!$this->title) {
            $this->title = ($this->amount > 0)
                ? 'Amount deposited'
                : 'Amount withdrawal';
        }

        $this->newTransaction();

        return $this;
    }

    public function withdraw(float $amount = null, bool $force = null, string $title = '')
    {
        $this->amount = $amount ? $amount : $this->amount;
        $this->force = !is_null($force) ? $force : $this->force;

        if ($this->amount < 0) {
            throw new \ErrorException('Withdrawal amount should be greater than 0');
        }
        if (!$this->force && $this->balance < $this->amount) {
            throw new \ErrorException('Insufficient Balance');
        }

        $this->title = $title ? $title : $this->title;
        if (!$this->title) {
            $this->title = 'Amount withdrawal';
        }

        $this->wallet->balance = $this->wallet->balance - $this->amount;
        $this->wallet->save();
        $this->balance = $this->wallet->balance;

        $this->newTransaction(amount: ($this->amount * -1));

        return $this;
    }

    public function forceWithdraw(float $amount = null, string $title = '')
    {
        $this->withdraw($amount, true, $title);
        return $this;
    }

    public function transfer(ModelsWallet|Wallet $to, float $amount = null, bool $force = false, string $title = '')
    {
        $orgWallet = $this->wallet;
        $this->withdraw(
            amount: $amount,
            force: $force,
            title: $title ? $title : 'Amount transferred'
        );

        $this->wallet = ($to instanceof ModelsWallet)
            ? $to
            : $to->get();

        $this->deposit(
            amount: $amount,
            title: 'Amount received'
        );
        $this->wallet = $orgWallet;

        return $this;
    }

    public function forceTransfer($to, float $amount = null, string $title = '')
    {
        $this->transfer(
            to: $to,
            amount: $amount,
            force: true,
            title: $title
        );
        return $this;
    }

    public function balance($amount = null)
    {
        if ($amount) {
            $this->amount = $amount;
            $this->deposit();
        }

        return $this->wallet->balance;
    }

    public function newTransaction(float $amount = null)
    {
        $amount = $amount ? $amount : $this->amount;
        $transactionalData = [
            'wallet_id' =>  $this->wallet->id,
            'amount'    =>  $amount,
            'title'     =>  $this->title,
            'remarks'   =>  $this->remarks,
        ];

        if ($this->referenceModel && is_subclass_of($this->referenceModel, '\Illuminate\Database\Eloquent\Model')) {
            if (!isset($this->referenceModel->transactions)) {
                throw new \ErrorException('Use HasWalletTransaction trait in ' . get_class($this->referenceModel));
            }
            $this->referenceModel->transactions()->create($transactionalData);
        } else {
            WalletTransaction::create($transactionalData);
        }
    }

    public function get()
    {
        return $this->wallet;
    }

    public function transactions($func=null)
    {
        if ($func) {
            $this->wallet->load(['transactions' => $func]);
        } else {
            $this->wallet->load(['transactions' => function ($query) {
                $query->latest();
            }]);
        }
        return $this->wallet->transactions;
    }
}
