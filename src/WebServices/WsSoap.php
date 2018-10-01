<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\WebServices;

use Mactronique\TestWs\Factory\ResultFactory;
use Symfony\Component\Console\Output\OutputInterface;

class WsSoap implements TestWebServicesInterface
{
    const CONFIG_KEY_ENV = 'env';
    const CONFIG_KEY_FONCTIONS = 'functions';
    const CONFIG_KEY_RESPONSE = 'response';
    const CONFIG_KEY_METHOD_CALL = 'methodCall';
    /**
     * @var array
     */
    private $config;

    /**
     * @var ResultFactory
     */
    private $factory;

    /**
     * WsSoap constructor.
     * @param array $config
     * @param ResultFactory $factory
     * @throws WebServiceException
     */
    public function __construct(array $config, ResultFactory $factory)
    {
        $this->config = $config;
        if (!array_key_exists(self::CONFIG_KEY_ENV,
                $this->config) || !is_array($this->config[self::CONFIG_KEY_ENV]) || 0 == count($this->config[self::CONFIG_KEY_ENV])) {
            throw new WebServiceException('Les environnements ne sont pas définit', 1);
        }

        if (!array_key_exists(self::CONFIG_KEY_FONCTIONS,
                $this->config) || !is_array($this->config[self::CONFIG_KEY_FONCTIONS]) || 0 == count($this->config[self::CONFIG_KEY_FONCTIONS])) {
            throw new WebServiceException('Les fonctions ne sont pas définit', 1);
        }

        if (!array_key_exists(self::CONFIG_KEY_RESPONSE,
                $this->config) || !is_array($this->config[self::CONFIG_KEY_RESPONSE]) || 0 == count($this->config[self::CONFIG_KEY_RESPONSE])) {
            throw new WebServiceException('Les informations de réponse ne sont pas définit', 1);
        }
        $this->factory = $factory;
    }

    /**
     * @throws \Exception si une erreur intervient.
     */
    public function runTests(OutputInterface $output)
    {
        $results = [];
        foreach ($this->config[self::CONFIG_KEY_ENV] as $env => $url) {
            $output->writeln('Test de l\'environnement <info>' . $env . '</info>');
            $results = array_merge($results, $this->testEnv($this->config['functions'], $url, $output, $env));
        }
        return $results;
    }

    /**
     * @param array $functions
     * @param $url
     * @param OutputInterface $output
     * @param $env
     * @return array
     */
    private function testEnv(array $functions, $url, OutputInterface $output, $env)
    {
        $options = array(
            'cache_wsdl' => 0,
            'trace' => 1,
            'soap_version' => SOAP_1_1,
            'user_agent' => 'Test Ws Client From PHP ' . PHP_VERSION
        );
        if (false === stripos($url, 'wsdl')) {
            $options['uri'] = 'http://test-uri/';
            $options['location'] = $url;
            $finalUrl = null;
        } else {
            $finalUrl = $url;
        }
        $soapClient = new \SoapClient($finalUrl, $options);
        $results = [];
        foreach ($functions as $function => $parameters) {
            $output->writeln('Test de la fonction soap <comment>' . $function . '</comment>');
            $output->writeln('Parameters : ');
            dump($parameters);
            $fullUrl = $url . "::" . $function;
            $stats = ['url' => $fullUrl];

            $start['started_at'] = microtime(true);
            $result = null;
            try {
                if (!array_key_exists(slef::CONFIG_KEY_METHOD_CALL,
                        $this->config) || $this->config[self::CONFIG_KEY_METHOD_CALL] == 'soapCall') {
                    $result = $soapClient->__soapCall($function, $parameters);
                } else {
                    $result = $soapClient->$function($parameters);
                }
            } catch (\Exception $e) {
                dump($e);
            }

            $stats['total_time'] = microtime(true) - $start['started_at'];

            $headers = $soapClient->__getLastResponseHeaders();
            //Séparation des entetes
            $headersArray = explode("\r\n", $headers);
            //Recherche de la réponse HTTP et traitement
            $httpVersion = "1.1";
            $httpResultCode = 200;
            $httpReason = null;
            if (preg_match('/^HTTP\/([0-2]{1}\.[0-9]{1}) ([0-9]{3}) (.*)$/', $headersArray[0], $matches)) {
                $httpVersion = $matches[1];
                $httpResultCode = intval($matches[2]);
                $httpReason = intval($matches[3]);
                unset($headersArray[0]);
            }
            //Transformation des entêtes
            $headersArray2 = [];
            foreach ($headersArray as $value) {
                if (preg_match('/^([a-zA-Z0-9\-\_]*): (.*)$/', $value, $matchesHeader)) {
                    //Entete déjà existante
                    if (isset($headersArray2[$matchesHeader[1]])) {
                        $headersArray2[$matchesHeader[1]][] = $matchesHeader[2];
                        continue;
                    }
                    //Ajout de l'entete
                    $headersArray2[$matchesHeader[1]] = [$matchesHeader[2]];
                }
            }
            $lastResponse = $soapClient->__getLastResponse();
            $responsePsr = new \GuzzleHttp\Psr7\Response($httpResultCode, $headersArray2, $lastResponse, $httpVersion,
                $httpReason);

            if ($output->isDebug()) {
                $output->writeln('Entete de la reponse : ');
                dump($headers);
            }

            $output->writeln('Reponse : ');
            dump($result);
            if ($output->isDebug()) {
                dump($lastResponse);
            }
            $results[$fullUrl] = $this->factory->makeResult($stats, $this->config[self::CONFIG_KEY_RESPONSE], $env,
                $responsePsr);
        }
        return $results;
    }
}
