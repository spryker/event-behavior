<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorBusinessFactory getFactory()
 */
class EventBehaviorFacade extends AbstractFacade implements EventBehaviorFacadeInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return void
     */
    public function triggerRuntimeEvents()
    {
        $this->getFactory()->createTriggerManager()->triggerRuntimeEvents();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return void
     */
    public function triggerLostEvents()
    {
        $this->getFactory()->createTriggerManager()->triggerLostEvents();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     *
     * @return array
     */
    public function getEventTransferIds(array $eventTransfers)
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransferIds($eventTransfers);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getEventTransferForeignKeys(array $eventTransfers, $foreignKeyColumnName)
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransferForeignKeys($eventTransfers, $foreignKeyColumnName);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param array $eventTransfers
     * @param array $columns
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns)
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransfersByModifiedColumns($eventTransfers, $columns);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param array $resources
     * @param array $ids
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = []): void
    {
        $this->getFactory()->createEventResourcePluginResolver()->executeResolvedPluginsBySources($resources, $ids);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return string[]
     */
    public function getAvailableResourceNames(): array
    {
        return $this->getFactory()->createEventResourcePluginResolver()->getAvailableResourceNames();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @example ['foreignKey1Value' => ['relatedForeignKeys1' => [foreignKey1 => foreignKey1Value, ...], ...]]
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getGroupedEventTransferForeignKeysByForeignKey(array $eventTransfers, string $foreignKeyColumnName): array
    {
        return $this->getFactory()
            ->createEventEntityTransferFilter()
            ->getGroupedEventTransferForeignKeysByForeignKey(
                $eventTransfers,
                $foreignKeyColumnName
            );
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param string $eventListenerName
     * @param string $transferData
     * @param string $format
     * @param string $eventName
     *
     * @return void
     */
    public function triggerEventListenerByName(string $eventListenerName, string $transferData, string $format, string $eventName): void
    {
        $this->getFactory()
            ->createListenerTrigger()
            ->triggerEventListenerByName($eventListenerName, $transferData, $format, $eventName);
    }
}
