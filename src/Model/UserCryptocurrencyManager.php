<?php

namespace Snowdog\Academy\Model;

use Snowdog\Academy\Core\Database;

class UserCryptocurrencyManager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getCryptocurrenciesByUserId(int $userId): array
    {
        $query = $this->database->prepare('SELECT c.id, c.name, uc.amount FROM user_cryptocurrencies AS uc LEFT JOIN cryptocurrencies AS c ON uc.cryptocurrency_id = c.id WHERE uc.user_id = :user_id');
        $query->bindParam(':user_id', $userId, Database::PARAM_INT);
        $query->execute();

        return $query->fetchAll(Database::FETCH_CLASS, UserCryptocurrency::class);
    }

    public function addCryptocurrencyToUser(int $userId, Cryptocurrency $cryptocurrency, int $amount): void
    {
        $userCryptocurrency = $this->getUserCryptocurrency($userId, $cryptocurrency->getId());
        if(!$userCryptocurrency) {
            $statement = $this->database->prepare('INSERT INTO user_cryptocurrencies (user_id, cryptocurrency_id, amount) VALUES (:user_id, :cryptocurrency_id, :amount)');
        }
        else {
            $amount+=$userCryptocurrency->getAmount();
            $statement = $this->database->prepare('UPDATE user_cryptocurrencies SET amount = :amount WHERE user_id = :user_id AND cryptocurrency_id = :cryptocurrency_id');
        }
        
        
        $statement->bindParam(':user_id', $userId, Database::PARAM_INT);
        $statement->bindParam(':cryptocurrency_id', $cryptocurrency->getId(), Database::PARAM_STR);
        $statement->bindParam(':amount', $amount, Database::PARAM_INT);
        $statement->execute();
    }

    public function subtractCryptocurrencyFromUser(int $userId, Cryptocurrency $cryptocurrency, int $amount): void
    {
        if ($amount > 0) {
            $statement = $this->database->prepare('UPDATE user_cryptocurrencies SET amount = :amount WHERE user_id = :user_id AND cryptocurrency_id = :cryptocurrency_id');
            $statement->bindParam(':amount', $amount, Database::PARAM_INT);
        } else {
            $statement = $this->database->prepare('DELETE FROM user_cryptocurrencies WHERE user_id = :user_id AND cryptocurrency_id = :cryptocurrency_id');
        }
        $statement->bindParam(':user_id', $userId, Database::PARAM_INT);
        $statement->bindParam(':cryptocurrency_id', $cryptocurrency->getId(), Database::PARAM_STR);
        $statement->execute();
    }

    public function getUserCryptocurrency(int $userId, string $cryptocurrencyId): ?UserCryptocurrency
    {
        $query = $this->database->prepare('SELECT * FROM user_cryptocurrencies WHERE user_id = :user_id AND cryptocurrency_id = :cryptocurrency_id');
        $query->bindParam(':user_id', $userId, Database::PARAM_INT);
        $query->bindParam(':cryptocurrency_id', $cryptocurrencyId, Database::PARAM_STR);
        $query->execute();

        /** @var UserCryptocurrency $result */
        $result = $query->fetchObject(UserCryptocurrency::class);

        return $result ?: null;
    }
}
