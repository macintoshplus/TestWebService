<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\WebServices;

use Mactronique\TestWs\Factory\ResultFactory;
use Symfony\Component\Console\Output\OutputInterface;

interface TestWebServicesInterface
{
    /**
     * TestWebServicesInterface constructor.
     * @param array $config
     * @param ResultFactory $factory
     */
    public function __construct(array $config, ResultFactory $factory);

    /**
     * @throws WebServiceException si une erreur intervient.
     * @return array
     */
    public function runTests(OutputInterface $output);
}
