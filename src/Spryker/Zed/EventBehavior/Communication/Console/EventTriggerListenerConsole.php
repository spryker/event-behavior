<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface getFacade()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\EventBehavior\Communication\EventBehaviorCommunicationFactory getFactory()
 */
class EventTriggerListenerConsole extends Console
{
    protected const NAME = 'event:trigger:listener';
    protected const DESCRIPTION = 'Triggers listener with provided event data.';

    protected const OPTION_LONG_FORMAT = 'format';
    protected const OPTION_SHORT_FORMAT = 'f';

    protected const ARGUMENT_LISTENER_NAME = 'listenerName';
    protected const ARGUMENT_DATA = 'data';

    protected const OPTION_EVENT_NAME = 'event';
    protected const OPTION_SHORT_EVENT_NAME = 'e';

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName(static::NAME)
            ->setDescription(static::DESCRIPTION)
            ->addArgument(static::ARGUMENT_LISTENER_NAME, InputArgument::REQUIRED, 'Event listener name.')
            ->addArgument(static::ARGUMENT_DATA, InputArgument::REQUIRED, 'Data for filling up an event transfers.')
            ->addOption(
                static::OPTION_LONG_FORMAT,
                static::OPTION_SHORT_FORMAT,
                InputOption::VALUE_OPTIONAL,
                'Input format data. Default is querystring. Json also supported.',
                'querystring'
            )
            ->addOption(
                static::OPTION_EVENT_NAME,
                static::OPTION_SHORT_EVENT_NAME,
                InputOption::VALUE_OPTIONAL,
                'An event name that should be triggered.',
                ''
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listenerName = $input->getArgument(static::ARGUMENT_LISTENER_NAME);
        $transferData = $input->getArgument(static::ARGUMENT_DATA);
        $format = $input->getOption(static::OPTION_LONG_FORMAT);
        $eventName = $input->getOption(static::OPTION_EVENT_NAME);

        $this->getFacade()->triggerEventListenerByName($listenerName, $transferData, $format, $eventName);

        return static::CODE_SUCCESS;
    }
}
