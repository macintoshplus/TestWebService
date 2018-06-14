<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Command;

use Mactronique\TestWs\Configuration\ConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Mactronique\TestWs\Configuration\MainConfiguration;
use Symfony\Component\Config\Definition\Processor;

class TestUnitCommand extends Command
{
    const CONFIG_KEY_WEBSERVICE = 'webservices';
    private $config;

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Execute les tests')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Nom du web service à tester'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws ConfigurationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = __DIR__.'/../webservices.yml';
        if (!file_exists($configFile)) {
            throw new ConfigurationException('Le fichier de configuration ('.$configFile.') est absent ! ', 123);
        }

        $configs = [Yaml::parse(file_get_contents($configFile))];
        $processor = new Processor();
        $configuration = new MainConfiguration();
        $this->config = $processor->processConfiguration($configuration, $configs);

        $ws = $this->getWebServiceToTest($input->getArgument('name'));

        $hostname = gethostname();
        if ($hostname === false) {
            $hostname = 'undefined hostname '.uniqid();
        }

        $factory = new \Mactronique\TestWs\Factory\ResultFactory($hostname);

        foreach ($ws as $name => $infos) {
            $output->writeln('Execution du test sur le web service <info>'.$name.'</info>');
            $class = $infos['class'];
            if (!class_exists($class)) {
                $this->getApplication()->renderException(new \Exception('La Classe "'.$class.'" n\'exite pas', 404), $output);
                continue;
            }
            try {
                $classTest = new $class($infos['config'], $factory);
                if (!$classTest instanceof \Mactronique\TestWs\WebServices\TestWebServicesInterface) {
                    throw new ConfigurationException("La classe de test n'implemente pas l'interface 'Mactronique\TestWs\WebServices\TestWebServicesInterface'", 1);
                }
                $results = $classTest->runTests($output);

                if (isset($infos['storage'])) {
                    $storageManager = new \Mactronique\TestWs\Persistance\StorageManager($infos['storage']);
                    $storageManager->save($results, $name);
                }
            } catch (\Exception $e) {
                $this->getApplication()->renderException($e, $output);
                continue;
            }
        }
        $output->writeln('Fin ! '.date('c'));
    }

    /**
     * @param string|null $name
     * @return array
     * @throws ConfigurationException
     */
    private function getWebServiceToTest($name = null)
    {
        if (null === $name) {
            return $this->config[self::CONFIG_KEY_WEBSERVICE];
        }

        if (!array_key_exists($name, $this->config[self::CONFIG_KEY_WEBSERVICE])) {
            throw new ConfigurationException("Le webservice $name est introuvable", 200);
        }

        return [$name => $this->config[self::CONFIG_KEY_WEBSERVICE][$name]];
    }
}
