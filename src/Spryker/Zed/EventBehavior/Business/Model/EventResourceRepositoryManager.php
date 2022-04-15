<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Iterator;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceAdditionalValuesRepositoryExtensionPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceForeignKeysRepositoryExtensionPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;
use function _HumbugBox60b1d604e02d\Amp\Iterator\concat;

class EventResourceRepositoryManager implements EventResourceManagerInterface
{
    /**
     * @var int
     */
    protected const DEFAULT_CHUNK_SIZE = 100;

    /**
     * @phpstan-var non-empty-string
     *
     * @var string
     */
    protected const DELIMITER = '.';

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    protected $eventFacade;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param int|null $chunkSize
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        ?int $chunkSize = null
    ) {
        $this->eventFacade = $eventFacade;
        $this->chunkSize = $chunkSize ?? static::DEFAULT_CHUNK_SIZE;
    }

    /**
     * @param array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface> $plugins
     * @param array<int> $ids
     *
     * @return void
     */
    public function processResourceEvents(array $plugins, array $ids = []): void
    {
        foreach ($plugins as $plugin) {
            if ($plugin instanceof EventResourceBulkRepositoryPluginInterface) {
                $this->processEventsForBulkRepositoryPlugins($plugin, $ids);

                continue;
            }

            if ($plugin instanceof EventResourceRepositoryPluginInterface) {
                $this->processEventsForRepositoryPlugins($plugin, $ids);
            }
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function processEventsForRepositoryPlugins(EventResourceRepositoryPluginInterface $plugin, array $ids = []): void
    {
        if ($ids !== [] && !$this->hasAdditionalValues($plugin)) {
            $this->triggerBulkIds($plugin, $ids);

            return;
        }

        $this->processEventsForRepositoryPlugin($plugin, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return void
     */
    protected function processEventsForBulkRepositoryPlugins(EventResourceBulkRepositoryPluginInterface $plugin, array $ids = []): void
    {
        if ($ids !== [] && !$this->hasAdditionalValues($plugin)) {
            $this->triggerBulkIds($plugin, $ids);

            return;
        }

        $this->processEventsForRepositoryBulkPlugins($plugin, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return void
     */
    protected function processEventsForRepositoryPlugin(EventResourceRepositoryPluginInterface $plugin, array $ids): void
    {
        foreach ($this->createEventResourceRepositoryPluginIterator($plugin, $ids) as $eventEntities) {
            if (!$this->hasAdditionalValues($plugin)) {
                $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $eventEntities);
                $this->triggerBulkIds($plugin, $eventEntitiesIds);

                continue;
            }
            $this->triggerBulk($plugin, $eventEntities);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return \Iterator<array<\Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer>>
     */
    protected function createEventResourceRepositoryPluginIterator(EventResourceRepositoryPluginInterface $plugin, array $ids): Iterator
    {
        return new EventResourceRepositoryPluginIterator($plugin, $this->chunkSize, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param array<int> $ids
     * @return void
     */
    protected function processEventsForRepositoryBulkPlugins(EventResourceBulkRepositoryPluginInterface $plugin, array $ids): void
    {
        foreach ($this->createEventResourceRepositoryBulkPluginIterator($plugin, $ids) as $eventEntities) {
            if (!$this->hasAdditionalValues($plugin)) {
                $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $eventEntities);
                $this->triggerBulkIds($plugin, $eventEntitiesIds);

                continue;
            }
            $this->triggerBulk($plugin, $eventEntities);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param array<int> $ids
     * 
     * @return \Iterator<array<\Spryker\Shared\Kernel\Transfer\AbstractTransfer>>
     */
    protected function createEventResourceRepositoryBulkPluginIterator(EventResourceBulkRepositoryPluginInterface $plugin, $ids): Iterator
    {
        return new EventResourceRepositoryBulkPluginIterator($plugin, $this->chunkSize, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array<\Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer> $chunkOfEventEntitiesTransfers
     *
     * @return array<int>
     */
    protected function getEventEntitiesIds($plugin, $chunkOfEventEntitiesTransfers): array
    {
        $eventEntitiesIds = [];

        foreach ($chunkOfEventEntitiesTransfers as $entitiesTransfer) {
            $entitiesTransferArray = $entitiesTransfer->modifiedToArray();
            $idColumnName = $this->getIdColumnName($plugin);
            $eventEntitiesIds[] = $entitiesTransferArray[$idColumnName];
        }

        return $eventEntitiesIds;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return string|null
     */
    protected function getIdColumnName($plugin): ?string
    {
        /** @phpstan-var array<int, string> $idColumnName */
        $idColumnName = explode(static::DELIMITER, (string)$plugin->getIdColumnName());

        return $idColumnName[1] ?? null;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param \Iterator<array<\Spryker\Shared\Kernel\Transfer\AbstractTransfer>> $transfers
     *
     * @return void
     */
    protected function triggerBulk(EventResourcePluginInterface $plugin, array $transfers): void
    {
        $idColumnName = $this->getIdColumnName($plugin);
        $additionalValues = [];
        $foreignKeys = [];

        if ($plugin instanceof EventResourceAdditionalValuesRepositoryExtensionPluginInterface) {
            $additionalValues = $plugin->getAdditionalValuesMapping();
        }
        if ($plugin instanceof EventResourceForeignKeysRepositoryExtensionPluginInterface) {
            $foreignKeys = $plugin->getForeignKeysMapping();
        }

        $eventEntityTransfers = array_map(function (AbstractTransfer $transfer) use ($idColumnName, $foreignKeys, $additionalValues) {
            $transferArray = $transfer->modifiedToArray();
            $transferInCamelCaseArray = $transfer->modifiedToArray(true, true);
            $eventEntityTransfer = (new EventEntityTransfer())
                ->setId($transferArray[$idColumnName]);

            $eventEntityTransfer->setForeignKeys(
                $this->mapAdditionalFiles($foreignKeys, $transferInCamelCaseArray)
            );
            $eventEntityTransfer->setAdditionalValues(
                $this->mapAdditionalFiles($additionalValues, $transferInCamelCaseArray)
            );

            return $eventEntityTransfer;
        }, $transfers);
        \var_dump(count($eventEntityTransfers),$eventEntityTransfers[0]->toArray());die;
        $this->eventFacade->triggerBulk($plugin->getEventName(), $eventEntityTransfers);
    }

    /**
     * @param array<string, string> $additionalValuesMapping
     * @param array<string, mixed> $transferArray
     *
     * @return array
     */
    protected function mapAdditionalFiles(array $additionalValuesMapping, array $transferArray): array
    {
        $additionalValues = [];
        foreach ($additionalValuesMapping as $tableFieldName => $transferPropertyName) {
            $additionalValues[$tableFieldName] = $transferArray[$transferPropertyName];
        }

        return $additionalValues;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array<int> $ids
     *
     * @return void
     */
    protected function triggerBulkIds(EventResourcePluginInterface $plugin, array $ids): void
    {
        $eventEntityTransfers = array_map(function ($id) {
            return (new EventEntityTransfer())->setId($id);
        }, $ids);
        \var_dump(count($eventEntityTransfers),$eventEntityTransfers[0]->toArray());die;
        $this->eventFacade->triggerBulk($plugin->getEventName(), $eventEntityTransfers);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     *
     * @return bool
     */
    protected function hasAdditionalValues(EventResourceBulkRepositoryPluginInterface $plugin)
    {
        if (
            $plugin instanceof EventResourceAdditionalValuesRepositoryExtensionPluginInterface ||
            $plugin instanceof EventResourceForeignKeysRepositoryExtensionPluginInterface
        ) {
            return true;
        }

        return false;
    }
}
