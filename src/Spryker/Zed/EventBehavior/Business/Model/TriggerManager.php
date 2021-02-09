<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use DateInterval;
use DateTime;
use Generated\Shared\Transfer\EventEntityTransfer;
use Orm\Zed\EventBehavior\Persistence\Base\SpyEventBehaviorEntityChangeQuery as BaseSpyEventBehaviorEntityChangeQuery;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface;
use Spryker\Zed\EventBehavior\Persistence\Propel\Behavior\EventBehavior;
use Spryker\Zed\Kernel\RequestIdentifier;

class TriggerManager implements TriggerManagerInterface
{
    /**
     * @uses \Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap::TABLE_NAME
     */
    protected const TABLE_NAME_EVENT_BEHAVIOR_ENTITY_CHANGE = 'spy_event_behavior_entity_change';

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    protected $eventFacade;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface
     */
    protected $utilEncodingService;

    /**
     * @var \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @var \Spryker\Zed\EventBehavior\EventBehaviorConfig
     */
    protected $config;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface
     */
    protected $propelFacade;

    /**
     * @var bool|null
     */
    protected static $eventBehaviorTableExists;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface $utilEncodingService
     * @param \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface $queryContainer
     * @param \Spryker\Zed\EventBehavior\EventBehaviorConfig $config
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface $propelFacade
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        EventBehaviorToUtilEncodingInterface $utilEncodingService,
        EventBehaviorQueryContainerInterface $queryContainer,
        EventBehaviorConfig $config,
        EventBehaviorToPropelFacadeInterface $propelFacade
    ) {
        $this->eventFacade = $eventFacade;
        $this->utilEncodingService = $utilEncodingService;
        $this->queryContainer = $queryContainer;
        $this->config = $config;
        $this->propelFacade = $propelFacade;
    }

    /**
     * @return void
     */
    public function triggerRuntimeEvents()
    {
        if (static::$eventBehaviorTableExists === false) {
            return;
        }

        if (!$this->config->getEventBehaviorTriggeringStatus()) {
            return;
        }

        $processId = RequestIdentifier::getRequestId();
        if (!$this->eventBehaviorTableExists()) {
            static::$eventBehaviorTableExists = false;

            return;
        }

        $events = $this->queryContainer->queryEntityChange($processId)->find()->getData();
        static::$eventBehaviorTableExists = true;

        $triggeredRows = $this->triggerEvents($events);

        if ($triggeredRows !== 0 && count($events) === $triggeredRows) {
            $this->queryContainer->queryEntityChange($processId)->delete();
        }
    }

    /**
     * @return void
     */
    public function triggerLostEvents()
    {
        if (!$this->config->getEventBehaviorTriggeringStatus()) {
            return;
        }

        $defaultTimeout = sprintf('PT%dM', $this->config->getEventEntityChangeTimeout());
        $date = new DateTime();
        $date->sub(new DateInterval($defaultTimeout));

        $events = $this->queryContainer->queryLatestEntityChange($date)->find()->getData();
        $triggeredRows = $this->triggerEvents($events);

        if ($triggeredRows !== 0 && count($events) === $triggeredRows) {
            $this->queryContainer->queryLatestEntityChange($date)->delete();
        }
    }

    /**
     * @param \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange[] $events
     *
     * @return int
     */
    protected function triggerEvents(array $events): int
    {
        $triggeredRows = 0;
        $eventEntityTransfersByEvent = [];
        foreach ($events as $event) {
            $data = $this->utilEncodingService->decodeJson($event->getData(), true);
            $eventEntityTransfer = new EventEntityTransfer();
            $eventEntityTransfer->setEvent($data[EventBehavior::EVENT_CHANGE_NAME]);
            $eventEntityTransfer->setName($data[EventBehavior::EVENT_CHANGE_ENTITY_NAME]);
            $eventEntityTransfer->setId($data[EventBehavior::EVENT_CHANGE_ENTITY_ID]);
            $eventEntityTransfer->setForeignKeys($data[EventBehavior::EVENT_CHANGE_ENTITY_FOREIGN_KEYS]);
            if (isset($data[EventBehavior::EVENT_CHANGE_ENTITY_ORIGINAL_VALUES])) {
                $eventEntityTransfer->setOriginalValues($data[EventBehavior::EVENT_CHANGE_ENTITY_ORIGINAL_VALUES]);
            }
            if (isset($data[EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS])) {
                $eventEntityTransfer->setModifiedColumns($data[EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS]);
            }
            if (isset($data[EventBehavior::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES])) {
                $eventEntityTransfer->setAdditionalValues($data[EventBehavior::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES]);
            }
            $eventEntityTransfersByEvent[$data[EventBehavior::EVENT_CHANGE_NAME]][] = $eventEntityTransfer;
            $triggeredRows++;
        }

        /**
         * @var string $eventName
         */
        foreach ($eventEntityTransfersByEvent as $eventName => $eventEntityTransfers) {
            $this->eventFacade->triggerBulk($eventName, $eventEntityTransfers);
        }

        return $triggeredRows;
    }

    /**
     * @return bool
     */
    protected function eventBehaviorTableExists(): bool
    {
        return class_exists(BaseSpyEventBehaviorEntityChangeQuery::class)
            && class_exists(SpyEventBehaviorEntityChangeQuery::class)
            && $this->propelFacade->tableExists(static::TABLE_NAME_EVENT_BEHAVIOR_ENTITY_CHANGE);
    }
}
