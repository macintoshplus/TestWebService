<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Mactronique\TestWs\Configuration\MainConfiguration;
use Symfony\Component\Config\Definition\Processor;

class TestUnitCommand extends Command
{
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
            // ->addOption(
            //    'hostname',
            //    null,
            //    InputOption::VALUE_NONE,
            //    'If set, the task will yell in uppercase letters'
            // )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = __DIR__.'/../webservices.yml';
        if (!file_exists($configFile)) {
            throw new \Exception('Le fichier de configuration ($configFile) est absent ! ', 123);
        }

        $config = Yaml::parse(file_get_contents($configFile));

        $configs = [$config];
        $processor = new Processor();
        $configuration = new MainConfiguration();
        $this->config = $processor->processConfiguration($configuration, $configs);

        $ws = $this->getWebServiceToTest($input->getArgument('name'));

        foreach ($ws as $name => $infos) {
            $output->writeln('Execution du test sur le web service <info>'.$name.'</info>');
            $class = $infos['class'];
            if (!class_exists($class)) {
                $this->getApplication()->renderException(new \Exception('La Classe "'.$class.'" n\'exite pas', 404), $output);
                continue;
            }
            try {
                $classTest = new $class($infos['config']);
                if (!$classTest instanceof \Mactronique\TestWs\WebServices\TestWebServicesInterface) {
                    throw new \Exception("La classe de test n'implemente pas l'nterface 'Mactronique\TestWs\WebServices\TestWebServicesInterface'", 1);
                }
                $classTest->runTests($output);
            } catch (\Exception $e) {
                $this->getApplication()->renderException($e, $output);
                continue;
            }
        }
        $output->writeln('Fin !');
    }

    private function getWebServiceToTest($name = null)
    {
        if (null === $name) {
            return $this->config['webservices'];
        }

        if (!array_key_exists($name, $this->config['webservices'])) {
            throw new \Exception("Le webservice $name est introuvable", 200);
        }

        return [$name => $this->config['webservices'][$name]];
    }
}
