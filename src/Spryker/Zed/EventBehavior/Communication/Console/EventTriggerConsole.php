<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface getFacade()
 */
class EventTriggerConsole extends Console
{
    public const COMMAND_NAME = 'event:trigger';
    public const DESCRIPTION = 'Triggers events for publishing the resources';
    public const RESOURCE_OPTION = 'resource';
    public const RESOURCE_OPTION_SHORTCUT = 'r';
    public const RESOURCE_IDS_OPTION = 'ids';
    public const RESOURCE_IDS_OPTION_SHORTCUT = 'i';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption(static::RESOURCE_OPTION, static::RESOURCE_OPTION_SHORTCUT, InputArgument::OPTIONAL, 'Defines events of which resource(s) should be triggered, if there is more than one, use comma to separate them. 
        If not, full event triggering will be executed.');

        $this->addOption(static::RESOURCE_IDS_OPTION, static::RESOURCE_IDS_OPTION_SHORTCUT, InputArgument::OPTIONAL, 'Defines ids of entities which should be triggered, if there is more than one, use comma to separate them. 
        If not, all ids triggering will be executed.');

        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::DESCRIPTION);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resources = [];
        $resourcesIds = [];

        if ($input && $input->getOption(static::RESOURCE_OPTION)) {
            $resourceString = $input->getOption(static::RESOURCE_OPTION);
            $resources = explode(',', $resourceString);
        }

        if ($input && $input->getOption(static::RESOURCE_IDS_OPTION)) {
            $idsString = $input->getOption(static::RESOURCE_IDS_OPTION);
            $resourcesIds = explode(',', $idsString);
        }

        $this->getFacade()->executeResolvedPluginsBySources($resources, $resourcesIds);
    }
}
