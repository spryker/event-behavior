<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Iterator;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

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
        if ($ids !== []) {
            $this->triggerBulk($plugin, $ids);

            return;
        }

        if ($this->pluginHasData($plugin) === false) {
            $this->triggerPluginOnce($plugin);

            return;
        }

        $this->processEventsForRepositoryPlugin($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return void
     */
    protected function processEventsForBulkRepositoryPlugins(EventResourceBulkRepositoryPluginInterface $plugin, array $ids = []): void
    {
        if ($ids !== []) {
            $this->triggerBulk($plugin, $ids);

            return;
        }

        if ($this->pluginHasData($plugin) === false) {
            $this->triggerPluginOnce($plugin);

            return;
        }

        $this->processEventsForRepositoryBulkPlugins($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     *
     * @return void
     */
    protected function processEventsForRepositoryPlugin(EventResourceRepositoryPluginInterface $plugin): void
    {
        foreach ($this->createEventResourceRepositoryPluginIterator($plugin) as $eventEntities) {
            $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $eventEntities);
            $this->triggerBulk($plugin, $eventEntitiesIds);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     *
     * @return \Iterator<array<\Generated\Shared\Transfer\EventEntityTransfer>>
     */
    protected function createEventResourceRepositoryPluginIterator(EventResourceRepositoryPluginInterface $plugin): Iterator
    {
        return new EventResourceRepositoryPluginIterator($plugin, $this->chunkSize);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     *
     * @return void
     */
    protected function processEventsForRepositoryBulkPlugins(EventResourceBulkRepositoryPluginInterface $plugin): void
    {
        foreach ($this->createEventResourceRepositoryBulkPluginIterator($plugin) as $eventEntities) {
            $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $eventEntities);
            $this->triggerBulk($plugin, $eventEntitiesIds);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     *
     * @return \Iterator<array<\Generated\Shared\Transfer\EventEntityTransfer>>
     */
    protected function createEventResourceRepositoryBulkPluginIterator(EventResourceBulkRepositoryPluginInterface $plugin): Iterator
    {
        return new EventResourceRepositoryBulkPluginIterator($plugin, $this->chunkSize);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $chunkOfEventEntitiesTransfers
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
     * @param array<int> $ids
     *
     * @return void
     */
    protected function triggerBulk(EventResourcePluginInterface $plugin, array $ids): void
    {
        $eventEntityTransfers = array_map(function ($id) {
            return (new EventEntityTransfer())->setId($id);
        }, $ids);

        $this->eventFacade->triggerBulk($plugin->getEventName(), $eventEntityTransfers);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     *
     * @return void
     */
    protected function triggerPluginOnce(EventResourcePluginInterface $plugin): void
    {
        $this->eventFacade->trigger($plugin->getEventName(), new EventEntityTransfer());
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     *
     * @return bool
     */
    public function pluginHasData(EventResourceBulkRepositoryPluginInterface $plugin): bool
    {
        return $plugin->getData(0, static::DEFAULT_CHUNK_SIZE) !== [];
    }
}
