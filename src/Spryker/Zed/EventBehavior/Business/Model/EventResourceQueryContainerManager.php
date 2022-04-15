<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Iterator;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use ReflectionClass;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;

class EventResourceQueryContainerManager implements EventResourceManagerInterface
{
    /**
     * @var int
     */
    protected const DEFAULT_CHUNK_SIZE = 100;

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
     * @param array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface> $plugins
     * @param array<int> $ids
     *
     * @return void
     */
    public function processResourceEvents(array $plugins, array $ids = []): void
    {
        foreach ($plugins as $plugin) {
            $this->triggerEvents($plugin, $ids);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return void
     */
    protected function triggerEvents(EventResourceQueryContainerPluginInterface $plugin, array $ids = []): void
    {
        foreach ($this->createEventResourceQueryContainerPluginIterator($plugin, $ids) as $entities) {
            $this->triggerBulk($plugin, $entities);
        }
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param array<int> $ids
     *
     * @return \Iterator<array<\Propel\Runtime\ActiveRecord\ActiveRecordInterface>>
     */
    protected function createEventResourceQueryContainerPluginIterator(EventResourceQueryContainerPluginInterface $plugin, $ids = []): Iterator
    {
        return new EventResourceQueryContainerPluginIterator($plugin, $this->chunkSize, $ids);
    }

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param array<\Propel\Runtime\ActiveRecord\ActiveRecordInterface> $entities
     *
     * @return void
     */
    protected function triggerBulk(EventResourceQueryContainerPluginInterface $plugin, array $entities): void
    {
        if (!$entities) {
            return;
        }

        $reflactionEntity = new ReflectionClass(current($entities));

        $protectForeignKeysMethod = $reflactionEntity->getMethod('getForeignKeys');
        $protectForeignKeysMethod->setAccessible(true);
        $protectAdditionalValuesMethod = $reflactionEntity->getMethod('getAdditionalValues');
        $protectAdditionalValuesMethod->setAccessible(true);

        $eventEntityTransfers = array_map(function (ActiveRecordInterface $entity) use ($plugin, $protectForeignKeysMethod, $protectAdditionalValuesMethod) {
            return (new EventEntityTransfer())
                ->setId($entity->getPrimaryKey())
                ->setEvent($plugin->getEventName())
                ->setForeignKeys($protectForeignKeysMethod->invokeArgs($entity, []))
                ->setAdditionalValues($protectAdditionalValuesMethod->invokeArgs($entity, []));
        }, $entities);

        $this->eventFacade->triggerBulk($plugin->getEventName(), $eventEntityTransfers);
    }
}
