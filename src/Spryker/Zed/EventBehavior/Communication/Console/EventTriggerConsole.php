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
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @deprecated Use {@link \Spryker\Zed\Publisher\Communication\Console\PublisherTriggerEventsConsole} instead.
 *
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface getFacade()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\EventBehavior\Communication\EventBehaviorCommunicationFactory getFactory()
 */
class EventTriggerConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'event:trigger';

    /**
     * @var string
     */
    public const DESCRIPTION = 'Triggers events for publishing the resources';

    /**
     * @var string
     */
    public const RESOURCE_OPTION = 'resource';

    /**
     * @var string
     */
    public const RESOURCE_OPTION_SHORTCUT = 'r';

    /**
     * @var string
     */
    public const RESOURCE_IDS_OPTION = 'ids';

    /**
     * @var string
     */
    public const RESOURCE_IDS_OPTION_SHORTCUT = 'i';

    /**
     * @var string
     */
    protected const WARNING_MESSAGE = "Don't run this command in production environment.";

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(static::RESOURCE_OPTION, static::RESOURCE_OPTION_SHORTCUT, InputArgument::OPTIONAL, 'Defines events of which resource(s) should be triggered, if there is more than one, use comma to separate them.
        If not, full event triggering will be executed.');

        $this->addOption(static::RESOURCE_IDS_OPTION, static::RESOURCE_IDS_OPTION_SHORTCUT, InputArgument::OPTIONAL, 'Defines ids of entities which should be triggered, if there is more than one, use comma to separate them.
        If not, all ids triggering will be executed.');

        $this->setName(static::COMMAND_NAME)
            ->setDescription(static::DESCRIPTION)
            ->addUsage(sprintf('-%s resource_name -%s 1,5', static::RESOURCE_OPTION_SHORTCUT, static::RESOURCE_IDS_OPTION_SHORTCUT))
            ->addUsage($this->getResourcesUsageText());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resources = [];
        $resourcesIds = [];

        if ($input->getOption(static::RESOURCE_OPTION)) {
            $resourceString = (string)$input->getOption(static::RESOURCE_OPTION);
            $resources = explode(',', $resourceString);
        }

        if (empty($resources)) {
            $this->displayWarningMessage($input, $output);
        }

        if ($input->getOption(static::RESOURCE_IDS_OPTION)) {
            $idsString = (string)$input->getOption(static::RESOURCE_IDS_OPTION);
            /** @var array<int> $resourcesIds */
            $resourcesIds = explode(',', $idsString);
        }

        $this->getFacade()->executeResolvedPluginsBySources($resources, $resourcesIds);

        return static::CODE_SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function displayWarningMessage(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning(static::WARNING_MESSAGE);
    }

    /**
     * @return string
     */
    protected function getResourcesUsageText(): string
    {
        $availableResourceNames = $this->getFacade()->getAvailableResourceNames();

        return sprintf(
            "-%s [\n\t%s\n]",
            static::RESOURCE_OPTION_SHORTCUT,
            implode(",\n\t", $availableResourceNames),
        );
    }
}
