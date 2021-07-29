<?php

namespace Snowdog\Academy\Command;

use Exception;
use Snowdog\Academy\Core\Migration;
use Snowdog\Academy\Model\CryptocurrencyManager;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePrices
{
    private CryptocurrencyManager $cryptocurrencyManager;

    public function __construct(CryptocurrencyManager $cryptocurrencyManager)
    {
        $this->cryptocurrencyManager = $cryptocurrencyManager;
    }

    public function __invoke(OutputInterface $output)
    {
        // TODO
        // use $this->cryptocurrencyManager->updatePrice() method

        $ch = curl_init();
        foreach ($this->cryptocurrencyManager->getAllCryptocurrencies() as $cryptocurrency) {
            $id = $cryptocurrency->getId();
            $uri = 'https://api.coingecko.com/api/v3/simple/price?ids=' . $id . '&vs_currencies=usd';
            curl_setopt($ch, CURLOPT_URL, $uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            if ($http_code != '200') {
                $output->writeln('Error: response - ' . $id);
            } else {

                $response = json_decode($response, true);
                if (!is_array($response) || !array_key_exists('usd', $response[$id])) {
                    $output->writeln('Error: data - ' . $id);
                } else {
                    $price = $response[$id]['usd'];

                    $this->cryptocurrencyManager->updatePrice($id, $price);
                    $output->writeln($id . ' - updated price: ' . $price . ' USD');
                }
            }
        }
        curl_close($ch);
    }
}
