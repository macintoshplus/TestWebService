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
use Mactronique\TestWs\Factory\ResultFactory;

interface TestWebServicesInterface
{
    public function __construct(array $config, ResultFactory $factory);

    /**
     * @throws \Exception si une erreur intervient.
     * @return array
     */
    public function runTests(OutputInterface $output);
}
