<?php

namespace Snowdog\Academy\Controller;

use Snowdog\Academy\Model\Cryptocurrency;
use Snowdog\Academy\Model\CryptocurrencyManager;
use Snowdog\Academy\Model\UserCryptocurrencyManager;
use Snowdog\Academy\Model\UserManager;

class Cryptos
{
    private CryptocurrencyManager $cryptocurrencyManager;
    private UserCryptocurrencyManager $userCryptocurrencyManager;
    private UserManager $userManager;
    private Cryptocurrency $cryptocurrency;

    public function __construct(
        CryptocurrencyManager $cryptocurrencyManager,
        UserCryptocurrencyManager $userCryptocurrencyManager,
        UserManager $userManager
    ) {
        $this->cryptocurrencyManager = $cryptocurrencyManager;
        $this->userCryptocurrencyManager = $userCryptocurrencyManager;
        $this->userManager = $userManager;
    }

    public function index(): void
    {
        require __DIR__ . '/../view/cryptos/index.phtml';
    }

    public function buy(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }

        $this->cryptocurrency = $cryptocurrency;
        require __DIR__ . '/../view/cryptos/buy.phtml';
    }

    public function buyPost(string $id): void
    {
        // TODO
        // verify if user is logged in
        // use $this->userCryptocurrencyManager->addCryptocurrencyToUser() method
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }
        $amount = $_POST['amount'];
        $this->userCryptocurrencyManager->addCryptocurrencyToUser($user->getId(), $cryptocurrency, $amount);

        $_SESSION['flash'] = 'You have bought '.$amount .' of '. $cryptocurrency->getId();

        header('Location: /cryptos');
    }

    public function sell(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /account');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /account');
            return;
        }

        $this->cryptocurrency = $cryptocurrency;
        require __DIR__ . '/../view/cryptos/sell.phtml';
    }

    public function sellPost(string $id): void
    {
        // TODO
        // verify if user is logged in
        // use $this->userCryptocurrencyManager->subtractCryptocurrencyFromUser() method

        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }

        $amount = $_POST['amount'];
        $userCryptocurrency = $this->userCryptocurrencyManager->getUserCryptocurrency($user->getId(), $id);
        $newUserCryptocurrencyAmount = $userCryptocurrency->getAmount() - $amount;

        if($newUserCryptocurrencyAmount < 0) {
            $_SESSION['flash'] = 'You can not sell more than you have';
            header('Location: /account');
            return;
        }

        $this->userCryptocurrencyManager->subtractCryptocurrencyFromUser($user->getId(), $cryptocurrency, $newUserCryptocurrencyAmount);
        $_SESSION['flash'] = 'You have sold '.$amount .' of '. $cryptocurrency->getId();

        header('Location: /cryptos');
    }

    public function getCryptocurrencies(): array
    {
        return $this->cryptocurrencyManager->getAllCryptocurrencies();
    }
}