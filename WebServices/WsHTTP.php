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
        $results = [];
        foreach ($this->config['env'] as $env => $url) {
            $output->writeln('Test de l\'environnement <info>'.$env.'</info>');
            $start = microtime(true);
            $result = $this->testEnv($this->config['datas'], $url, $output);
            $end = microtime(true);
            $duration = $end - $start;
            $result['time_start'] = $start;
            $result['time_end'] = $end;
            $result['time_duration'] = $duration;
            $output->writeln('Durée de la requête : <info>'.number_format($duration, 3, ",", " ")."</info>");
            $results[$env] = $result;
        }
        return $results;
    }

    private function testEnv(array $datas, $url, OutputInterface $output)
    {
        $result = ['code_http'=> null, 'mime'=> null, 'server_header'=>null];
        $client = new Client(['http_errors' => false]);
        $response = $client->request($datas['method'], $url, ['headers' => ['Content-Type' => $datas['mime']], 'body' => $datas['datas']]);
        if ($response->getStatusCode() != $this->config['response']['http_code']) {
            $output->writeln('<error>Code réponse incorrect : '.$response->getStatusCode().'</error>');
        }
        $result['code_http'] = $response->getStatusCode();
        
        if (!$response->hasHeader('Content-Type') || $response->getHeader('Content-Type')[0] != $this->config['response']['mime']) {
            $output->writeln('<error>Content type absent ou incorrect : '.($response->getHeader('Content-Type')[0]).'</error>');
        }
        $result['mime'] = $response->getHeader('Content-Type')[0];
        if ($response->hasHeader($this->config['response']['server_header'])) {
            $output->writeln('Valeur entête '.$this->config['response']['server_header'].' : <info>'.($response->getHeader($this->config['response']['server_header'])[0]).'</info>');
            $result['server_header'] = $response->getHeader($this->config['response']['server_header'])[0];
        } else {
            $output->writeln('<error>Header '.($this->config['response']['server_header']).' introuvable</error>');
        }
        return $result;
    }
}
