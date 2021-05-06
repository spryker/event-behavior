<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorBusinessFactory getFactory()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorEntityManagerInterface getEntityManager()
 */
class EventBehaviorFacade extends AbstractFacade implements EventBehaviorFacadeInterface
{
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string[] $columns
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns)
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransfersByModifiedColumns($eventTransfers, $columns);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersOriginalValues(array $eventTransfers, string $columnName): array
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransfersOriginalValues($eventTransfers, $columnName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersAdditionalValues(array $eventTransfers, string $columnName): array
    {
        return $this->getFactory()->createEventEntityTransferFilter()->getEventTransfersAdditionalValues($eventTransfers, $columnName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string[] $resources
     * @param (string|int)[] $ids
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = [], array $resourcePublisherPlugins = []): void
    {
        $this->getFactory()->createEventResourcePluginResolver()->executeResolvedPluginsBySources($resources, $ids, $resourcePublisherPlugins);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return string[]
     */
    public function getAvailableResourceNames(array $resourcePublisherPlugins = []): array
    {
        return $this->getFactory()->createEventResourcePluginResolver()->getAvailableResourceNames($resourcePublisherPlugins);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
