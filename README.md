# Introduction

Laravel package to manage user wallet system. This can also manage multiple wallets for a single user / model. It is a multi wallet system to manage different wallet balances and also track the transactions history, like: deposit or withdraw and each transaction can reference to other models,for eg: If amount has deducted for some Product's payment, that product model can also be tracked. Read further for the usage instructions. 

## Integration & Setup

Just like other php packages, install this using composer.

    composer require takshak/wallet

Run the migrations `php artisan migrate`. 

Add 'HasWallet' traits to the models which has the wallets, eg: User model.

    <?php

    namespace App\Models;

    use Takshak\Wallet\Traits\HasWallet;

    class User extends Authenticatable
    {
        use HasWallet;
    }
        
Add 'HasWalletTransaction' traits to the models which needs to be referenced for transactional records, eg: Product model.

    <?php

    namespace App\Models;

    use Takshak\Wallet\Traits\HasWalletTransaction;

    class Product extends Model
    {
        use HasWalletTransaction;
    }

## Wallet features

 - [Basic Usage](#basic-usage)
 - [Create wallet](#create-wallet)
 - [Get wallet](#get-wallet)
 - [Get Balance](#get-balance)
 - [Deposit to wallet](#deposit-to-wallet)
 - [Withdraw from wallet](#withdraw-from-wallet)
 - [Transfer to other wallet](#transfer-to-other-wallet)
 - [Get wallet transactions](#get-wallet-transactions)

Let's assume the our user has wallet system and we added 'HasWallet' trait to our User model. There will be two models 'Wallet' and 'WalletTransaction' which will be used for managing all the operations.

### Basic Usage

    # get wallet balance
    $user->wallet()->balance(); 

    # deposit 200 to wallet
    $user->wallet()->balance(200);
    $user->wallet()->deposit(200);

    # withdraw 200 from wallet
    $user->wallet()->balance(-200);
    $user->wallet()->withdraw(200);
    $user->wallet()->forceWithdraw(200);

    # transfer to another wallet
    $user->wallet()->transfer($user2->wallet(), 200); 
    $user->wallet()->forceTransfer($user2->wallet(), 200); 

### Create wallet

    $user->createWallet();

Above mentioned line will create a primary wallet with name: default and 0 balance. You can also pass other parameters when creating a wallet.

    $user->createWallet(
        name: 'default', 
        balance: 0, 
        is_primary: true, 
        remarks: ''
    );

When you access the wallet instance: `$user->wallet()`, if there will be no primary wallet it will create a primary wallet with above mentioned parameters and return the wallet instance to you.

### Get wallet

Wallet instance: `$user->wallet()` - This will return the `Takshak\Wallet\Service\Wallet` instance of primary wallet for further operations like: deposit, withdraw, transfer etc.

Other wallet instance: `$user->getWallet('other')` - You need to provide the name of other user's wallet which you have given at the time of creation. Primary wallet's name is 'default' which can also be accessed via `$user->wallet()`

Get wallet model instance: `$user->wallet()->get()` - It will return the 'Wallet' model.

Get all the wallets: `$user->wallets` - You will get all the wallet models collections.

Get primary wallet: `$user->primaryWallet` - You get the primary wallet model.

### Get Balance

You can get the primary wallet balance by `$user->wallet()->balance` or `$user->wallet()->balance()`;
You can also pass other parameters to deposit or withdraw balance which are optional and will return the updated balance like:

    # deposit 200, returns updated balance
    $user->wallet()->balance(200); 

    # withdraw 200, returns updated balance
    $user->wallet()->balance(-200); 

Getting other wallet's balance via wallet name:

    # get the balance
    $user->getWallet('other')->balance(); 

    # deposit 200, returns updated balance
    $user->wallet()->balance(200); 

    # withdraw 200, returns updated balance
    $user->wallet()->balance(-200); 

### Deposit to wallet

If you simply need to deposit 200 to wallet

    $user->wallet()->deposit(200); 

Deposit 200 to wallet with a transaction title

    $user->wallet()->deposit(200, 'Got reward of 200'); 

Deposit amount with some extra information and get the updated balance 

    $user->wallet()->amount(200)
        ->title('Got reward of 200')
        ->remarks('Some other information about the transaction')
        ->deposit()->balance;

### Withdraw from wallet

Withdraw works just like deposit but it will throw and arrow if you withdraw more than the available balance. If you want to withdraw anyhow then you can use force withdraw which will make the wallet balance in negative if there is not sufficient balance. Withdraw will not accept the negative amount. Some examples are:

    # simply withdraw 200
    $user->wallet()->withdraw(200); 

    # withdraw 200 with a transaction title
    $user->wallet()->title('Amount is paid for purchase')->withdraw(200); 

    # withdraw 200 with a transaction title
    $user->wallet()->withdraw(amount: 200, title: 'Amount is paid for purchase'); 

    # get balance after withdraw
    $user->wallet()->withdraw(200)->balance; 

    # force withdraw and get balance
    $user->wallet()->withdraw(200, true)->balance;

    # force withdraw and get balance
    $user->wallet()->forceWithdraw(200)->balance;

    # other method by setting all options
    $user->wallet()
        ->amount(200) // amount to be withdraw
        ->title('Got reward of 200') // title of transaction
        ->remarks('Information about the transaction') // remarks of the transaction
        ->referenceModel($product) // model for transaction reference
        ->forceWithdraw() // make force withdraw
        ->balance; // get the balance

### Transfer to other wallet
You can transfer balance to user's another wallet or may be be another's user wallet. It will throw and arrow if you transfer more than the available balance. If you want to transfer anyhow then you can use force transfer which will make the wallet balance in negative if there is not sufficient balance. You are not allowed to pass negative amount. Some Examples:

    # transfer 200 to user's wallet
    $user->wallet()->amount(200)->transfer($user->getWallet('other'));

    # transfer 200 to $user2's wallet
    $user->wallet()->amount(200)->transfer($user2->wallet());

    $user->wallet()->transfer($user2->wallet(), 200);

    # force transfer 200 
    $user->wallet()->amount(200)
        ->force(true) // force transfer
        ->transfer($user2->wallet());

    $user->wallet()->amount(200)
        ->forceTransfer($user2->wallet()); // force transfer

    # force transfer with some title and remarks and get updated balance
    $user->wallet()
        ->amount(200) // amount to be transfer
        ->title('Got reward of 200') // title of transaction
        ->remarks('Information about the transaction') // remarks of the transaction
        ->force(true) // force transfer
        ->transfer($user2->wallet()) // transfer amount
        ->balance;

### Get wallet transactions

You can get the transactions list via 'WalletTransaction' model and do the filter as your requirement, or you can call the relation as:

    $user->wallet()->transactions();
    $user->getWallet('other')->transactions();

    $user->wallet()->get()->transactions;
    $user->primaryWallet->transactions;

    # digging with a closure
    $user->wallet()->transactions(function($query){
        $query->select('id', 'wallet_id', 'amount')->latest();
    });

    # via calling the model
    WalletTransaction::where('wallet_id', $user->wallet()->get()->id)->get();
