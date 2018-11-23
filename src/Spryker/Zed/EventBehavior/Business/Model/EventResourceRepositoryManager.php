<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

class EventResourceRepositoryManager implements EventResourceManagerInterface
{
    protected const DEFAULT_CHUNK_SIZE = 100;
    protected const ID_NULL = null;
    protected const DELIMITER = '.';

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    protected $eventFacade;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected $eventResourcePlugins;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param array $eventResourcePlugins
     * @param int|null $chunkSize
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        array $eventResourcePlugins,
        ?int $chunkSize = null
    ) {
        $this->eventFacade = $eventFacade;
        $this->eventResourcePlugins = $eventResourcePlugins;
        $this->chunkSize = $chunkSize ?? static::DEFAULT_CHUNK_SIZE;
    }

    /**
     * @param array $plugins
     * @param int[] $ids
     *
     * @return void
     */
    public function triggerResourceEvents(array $plugins, array $ids = []): void
    {
        foreach ($plugins as $plugin) {
            if ($plugin instanceof EventResourceRepositoryPluginInterface) {
                $this->triggerEvents($plugin, $ids);
                continue;
            }

            if ($plugin instanceof EventResourceBulkRepositoryPluginInterface) {
                $this->triggerEventsBulk($plugin, $ids);
            }
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function triggerEvents(EventResourceRepositoryPluginInterface $plugin, array $ids = []): void
    {
        if ($ids) {
            $this->trigger($plugin, $ids);

            return;
        }

        if (!$plugin->getData($ids)) {
            $this->trigger($plugin, [static::ID_NULL]);

            return;
        }

        $this->triggerEventsAll($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param int[] $ids
     *
     * @return void
     */
    protected function triggerEventsBulk(EventResourceBulkRepositoryPluginInterface $plugin, array $ids = []): void
    {
        if ($ids) {
            $this->trigger($plugin, $ids);

            return;
        }

        $this->triggerEventsAllBulk($plugin);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAll(EventResourceRepositoryPluginInterface $plugin): void
    {
        $eventEntities = $plugin->getData();
        $count = count($eventEntities);
        $loops = $count / $this->chunkSize;
        $offset = 0;

        for ($i = 0; $i < $loops; $i++) {
            $chunkOfEventEntitiesTransfers = array_slice($eventEntities, $offset, $this->chunkSize);
            $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $chunkOfEventEntitiesTransfers);
            $this->trigger($plugin, $eventEntitiesIds);
            $offset += $this->chunkSize;
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     *
     * @return void
     */
    protected function triggerEventsAllBulk(EventResourceBulkRepositoryPluginInterface $plugin): void
    {
        $offset = 0;

        do {
            $eventEntities = $plugin->getData($offset, $this->chunkSize);

            if (empty($eventEntities)) {
                $this->trigger($plugin, [static::ID_NULL]);
                break;
            }

            $eventEntitiesIds = $this->getEventEntitiesIds($plugin, $eventEntities);
            $this->trigger($plugin, $eventEntitiesIds);
            $offset += $this->chunkSize;
        } while (count($eventEntities) === $this->chunkSize);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $chunkOfEventEntitiesTransfers
     *
     * @return array
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
        $idColumnName = explode(static::DELIMITER, $plugin->getIdColumnName());
        return $idColumnName[1] ?? null;
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface $plugin
     * @param array $ids
     *
     * @return void
     */
    protected function trigger(EventResourcePluginInterface $plugin, array $ids): void
    {
        foreach ($ids as $id) {
            $eventEntityTransfer = (new EventEntityTransfer())->setId($id);
            $this->eventFacade->trigger($plugin->getEventName(), $eventEntityTransfer);
        }
    }
}
