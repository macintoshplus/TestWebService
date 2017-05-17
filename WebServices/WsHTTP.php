<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\WebServices;

use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Mactronique\TestWs\Model\WsQueryResult;

class WsHTTP implements TestWebServicesInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (!array_key_exists('env', $this->config) || !is_array($this->config['env']) || 0 == count($this->config['env'])) {
            throw new \Exception('Les environnements ne sont pas définis', 1);
        }

        if (!array_key_exists('datas', $this->config) || !is_array($this->config['datas']) || 0 == count($this->config['datas'])) {
            throw new \Exception('Les données ne sont pas définies', 1);
        }

        if (!array_key_exists('response', $this->config) || !is_array($this->config['response']) || 0 == count($this->config['response'])) {
            throw new \Exception('Les données de la réponse ne sont pas définies', 1);
        }
    }

    /**
     * @throws \Exception si une erreur intervient.
     * @return array
     */
    public function runTests(OutputInterface $output)
    {
        $requests = [];
        $statsAll = [];
        $statsData = [];
        $client = new Client(['http_errors' => false, 'timeout' => 12.0]);
        $output->writeln("Start : ".date('c'));
        $env = $this->config['env'];
        $dataRequest = $this->config['datas'];
        $responseData = $this->config['response'];
        $promises = (function () use ($env, $dataRequest, &$statsAll, $client, $responseData, &$statsData) {
            foreach ($env as $key => $url) {
                $statsAll[$url] = ['started_at'=>microtime(true)];
                yield $client->requestAsync($dataRequest['method'], $url, ['headers' => ['Content-Type' => $dataRequest['mime']], 'body' => $dataRequest['datas'], 'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$statsAll, $url, $responseData, &$statsData) {
                    //echo $stats->getEffectiveUri()." => ".$url."\n";
                    //echo "Transfert time : " . $stats->getTransferTime()."\n";
                    //var_dump($stats->getHandlerStats());
                    if (isset($statsAll[$url]) && is_array($statsAll[$url])) {
                        $statsArray = array_merge($statsAll[$url], $stats->getHandlerStats());
                    } else {
                        $statsArray = $stats->getHandlerStats();
                    }
                    $statsAll[$url] = $statsArray;
                    $statsData[$url] = new WsQueryResult($statsArray, $responseData, $stats->getResponse());
                },]);
            }
        })();

        //$output->writeln("All query launch : ".date('c'));

        //$statsData = [];
        $responseData = $this->config['response'];
        (new \GuzzleHttp\Promise\EachPromise($promises, [
            'concurrency' => 10
        ]))->promise()->wait();
        //$output->writeln("All query requested : ".date('c'));


        //$output->writeln("All response worked : ".date('c'));

        return $statsData;
    }
}
