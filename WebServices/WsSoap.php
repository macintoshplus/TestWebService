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

class WsSoap implements TestWebServicesInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (!array_key_exists('env', $this->config) || !is_array($this->config['env']) || 0 == count($this->config['env'])) {
            throw new \Exception('Les environnements ne sont pas définit', 1);
        }

        if (!array_key_exists('functions', $this->config) || !is_array($this->config['functions']) || 0 == count($this->config['functions'])) {
            throw new \Exception('Les fonctions ne sont pas définit', 1);
        }
    }

    /**
     * @throws \Exception si une erreur intervient.
     */
    public function runTests(OutputInterface $output)
    {
        foreach ($this->config['env'] as $env => $url) {
            $output->writeln('Test de l\'environnement <info>'.$env.'</info>');
            $this->testEnv($this->config['functions'], $url, $output);
        }
    }

    private function testEnv(array $functions, $url, OutputInterface $output)
    {
        $soapClient = new \SoapClient($url, array('cache_wsdl' => 0, 'trace' => 1, 'soap_version' => SOAP_1_1, 'user_agent'=> 'Test Ws Client From PHP '.PHP_VERSION));

        foreach ($functions as $function => $parameters) {
            $output->writeln('Test de la fonction soap <comment>'.$function.'</comment>');
            $output->writeln('Parameters : ');
            dump($parameters);

            if (!array_key_exists('methodCall', $this->config) || $this->config['methodCall'] == 'soapCall') {
                $result = $soapClient->__soapCall($function, $parameters);
            } else {
                $result = $soapClient->$function($parameters);
            }

            $headers = $soapClient->__getLastResponseHeaders();

            if ($output->isDebug()) {
                $output->writeln('Entete de la reponse : ');
                dump($headers);
            }

            if (false === $site = $this->getSiteHeader($headers)) {
                throw new \Exception('Site Header introuvable dans la réponse.', 1);
            }
            $output->writeln('Serveur ayant repondu : <info>'.$site.'</info>');

            $output->writeln('Reponse : ');
            dump($result);
            if ($output->isDebug()) {
                dump($soapClient->__getLastResponse());
            }
        }
    }

    private function getSiteHeader($headers)
    {
        if (!preg_match('/Site: (.*)\n/i', $headers, $matches)) {
            return false;
        }

        return $matches[1];
    }
}
