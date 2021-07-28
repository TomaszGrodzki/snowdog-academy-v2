<?php

namespace Snowdog\Academy\Controller;

use Snowdog\Academy\Model\User;
use Snowdog\Academy\Model\UserCryptocurrencyManager;
use Snowdog\Academy\Model\UserManager;

class Account
{
    private UserCryptocurrencyManager $userCryptocurrencyManager;
    private UserManager $userManager;
    private User $user;

    public function __construct(UserCryptocurrencyManager $userCryptocurrencyManager, UserManager $userManager)
    {
        $this->userCryptocurrencyManager = $userCryptocurrencyManager;
        $this->userManager = $userManager;
    }

    public function index(): void
    {
        $user = $this->userManager->getByLogin($_SESSION['login']);
        if (!$user) {
            header('Location: /login');
            return;
        }

        $this->user = $user;
        require __DIR__ . '/../view/account/index.phtml';
    }

    public function getUserCryptocurrencies(): array
    {
        $user = $this->userManager->getByLogin($_SESSION['login']);
        if (!$user->getId()) {
            return [];
        }

        return $this->userCryptocurrencyManager->getCryptocurrenciesByUserId($user->getId());
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addFunds(): void
    {
        $user = $this->userManager->getByLogin($_SESSION['login']);
        if (!$user) {
            header('Location: /login');
            return;
        }
        $this->user = $user;
        require __DIR__ . '/../view/account/addfunds.phtml';
    }

    public function addFundsPost(): void
    {
        $user = $this->userManager->getByLogin($_SESSION['login']);
        if (!$user) {
            header('Location: /login');
            return;
        }
        $this->user = $user;
        $funds = $this->user->getFunds() + $_POST['amount'];

        $this->userManager->updateFunds($this->user->getId(), $funds);
        $_SESSION['flash'] = 'Added '.$_POST['amount']. ' USD to your account';
        header('Location: /cryptos');
    }
}