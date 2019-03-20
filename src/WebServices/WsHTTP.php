<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\WebServices;

use GuzzleHttp\Client;
use Mactronique\TestWs\Factory\ResultFactory;
use Symfony\Component\Console\Output\OutputInterface;

class WsHTTP implements TestWebServicesInterface
{
    const CONFIG_KEY_ENV = 'env';
    const CONFIG_KEY_DATAS = 'datas';
    const CONFIG_KEY_RESPONSE = 'response';
    /**
     * @var array
     */
    private $config;

    /**
     * @var ResultFactory
     */
    private $factory;

    /**
     * WsHTTP constructor.
     * @param array $config
     * @param ResultFactory $factory
     * @throws WebServiceException
     */
    public function __construct(array $config, ResultFactory $factory)
    {
        $this->config = $config;
        if (
            !array_key_exists(self::CONFIG_KEY_ENV, $this->config) ||
            !is_array($this->config[self::CONFIG_KEY_ENV]) ||
            0 == count($this->config[self::CONFIG_KEY_ENV])
        ) {
            throw new WebServiceException('Les environnements ne sont pas définis', 1);
        }

        if (
            !array_key_exists(self::CONFIG_KEY_DATAS, $this->config) ||
            !is_array($this->config[self::CONFIG_KEY_DATAS]) ||
            0 == count($this->config[self::CONFIG_KEY_DATAS])
        ) {
            throw new WebServiceException('Les données ne sont pas définies', 1);
        }

        if (
            !array_key_exists(self::CONFIG_KEY_RESPONSE, $this->config) ||
            !is_array($this->config[self::CONFIG_KEY_RESPONSE]) ||
            0 == count($this->config[self::CONFIG_KEY_RESPONSE])
        ) {
            throw new WebServiceException('Les données de la réponse ne sont pas définies', 1);
        }
        $this->factory = $factory;
    }

    /**
     * @throws \Exception si une erreur intervient.
     * @return array
     */
    public function runTests(OutputInterface $output)
    {
        $statsAll = [];
        $statsData = [];
        $resultFactory = $this->factory;
        $client = new Client(['http_errors' => false, 'timeout' => 12.0]);
        $output->writeln("Start : " . date('c'));
        $env = $this->config['env'];
        $dataRequest = $this->config[self::CONFIG_KEY_DATAS];
        $responseData = $this->config[self::CONFIG_KEY_RESPONSE];
        $promises = (function () use (
            $env,
            $dataRequest,
            &$statsAll,
            $client,
            $responseData,
            &$statsData,
            $resultFactory
        ) {
            foreach ($env as $keyEnv => $url) {
                $statsAll[$url] = ['started_at' => microtime(true)];
                $headers = [];
                if (isset($dataRequest['mime'])) {
                    $headers['Content-Type'] = $dataRequest['mime'];
                }
                if (isset($dataRequest['authorization'])) {
                    $headers['Authorization'] = $dataRequest['authorization'];
                }

                yield $client->requestAsync($dataRequest['method'], $url, [
                    'headers' => $headers,
                    'body' => $dataRequest['datas'],
                    'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (
                        &$statsAll,
                        $url,
                        $responseData,
                        &$statsData,
                        $resultFactory,
                        $keyEnv
                    ) {

                        if (isset($statsAll[$url]) && is_array($statsAll[$url])) {
                            $statsArray = array_merge($statsAll[$url], $stats->getHandlerStats());
                        } else {
                            $statsArray = $stats->getHandlerStats();
                        }
                        $statsAll[$url] = $statsArray;
                        $statsData[$url] = $resultFactory->makeResult($statsArray, $responseData, $keyEnv,
                            $stats->getResponse());
                    },
                ]);
            }
        })();

        $responseData = $this->config[self::CONFIG_KEY_RESPONSE];
        (new \GuzzleHttp\Promise\EachPromise($promises, [
            'concurrency' => 10
        ]))->promise()->wait();

        return $statsData;
    }
}
