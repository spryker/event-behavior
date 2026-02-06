<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use DateInterval;
use DateTime;
use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\EventTriggerResponseTransfer;
use Orm\Zed\EventBehavior\Persistence\Base\SpyEventBehaviorEntityChangeQuery as BaseSpyEventBehaviorEntityChangeQuery;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\EventBehavior\Persistence\EventBehaviorEntityManagerInterface;
use Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface;
use Spryker\Zed\EventBehavior\Persistence\Propel\Behavior\EventBehavior;
use Spryker\Zed\Kernel\Persistence\EntityManager\InstancePoolingTrait;
use Spryker\Zed\Kernel\RequestIdentifier;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

class TriggerManager implements TriggerManagerInterface
{
    use InstancePoolingTrait;
    use ActiveRecordBatchProcessorTrait;

    /**
     * @uses \Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap::TABLE_NAME
     *
     * @var string
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
     * @var \Spryker\Zed\EventBehavior\Persistence\EventBehaviorEntityManagerInterface
     */
    protected $eventBehaviorEntityManager;

    /**
     * @var bool|null
     */
    protected static ?bool $eventBehaviorTableExists = null;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface $utilEncodingService
     * @param \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface $queryContainer
     * @param \Spryker\Zed\EventBehavior\EventBehaviorConfig $config
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface $propelFacade
     * @param \Spryker\Zed\EventBehavior\Persistence\EventBehaviorEntityManagerInterface $eventBehaviorEntityManager
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        EventBehaviorToUtilEncodingInterface $utilEncodingService,
        EventBehaviorQueryContainerInterface $queryContainer,
        EventBehaviorConfig $config,
        EventBehaviorToPropelFacadeInterface $propelFacade,
        EventBehaviorEntityManagerInterface $eventBehaviorEntityManager
    ) {
        $this->eventFacade = $eventFacade;
        $this->utilEncodingService = $utilEncodingService;
        $this->queryContainer = $queryContainer;
        $this->config = $config;
        $this->propelFacade = $propelFacade;
        $this->eventBehaviorEntityManager = $eventBehaviorEntityManager;
    }

    /**
     * @return void
     */
    public function triggerRuntimeEvents()
    {
        if (!$this->config->getEventBehaviorTriggeringStatus()) {
            return;
        }

        if (!$this->eventBehaviorTableExists()) {
            return;
        }

        $processId = RequestIdentifier::getRequestId();

        $limit = $this->config->getTriggerChunkSize();

        do {
            $events = $this->getEventEntitiesByProcessId($processId, $limit);
            $currentCountEvents = count($events);

            $this->triggerEventsAndDelete($events);
        } while ($currentCountEvents === $limit);
    }

    /**
     * @return \Generated\Shared\Transfer\EventTriggerResponseTransfer
     */
    public function triggerRuntimeEventsWithReport(): EventTriggerResponseTransfer
    {
        $eventTriggerResponseTransfer = new EventTriggerResponseTransfer();
        $eventTriggerResponseTransfer->setIsSuccessful(false);
        $eventTriggerResponseTransfer->setEventBehaviorTableExists(true);
        $eventTriggerResponseTransfer->setIsEventTriggeringActive(true);

        if ($this->eventBehaviorTableExists() === false) {
            $eventTriggerResponseTransfer->setMessage('Event behavior table does not exist.');
            $eventTriggerResponseTransfer->setEventBehaviorTableExists(false);

            return $eventTriggerResponseTransfer;
        }

        if (!$this->config->getEventBehaviorTriggeringStatus()) {
            $eventTriggerResponseTransfer->setMessage('Event triggering is not enabled.');
            $eventTriggerResponseTransfer->setIsEventTriggeringActive(false);

            return $eventTriggerResponseTransfer;
        }

        $requestId = RequestIdentifier::getRequestId();

        $eventTriggerResponseTransfer->setRequestId($requestId);

        $deletedRows = 0;
        $limit = $this->config->getTriggerChunkSize();
        do {
            $events = $this->queryContainer->queryEntityChange($requestId)->limit($limit)->find()->getData();
            $countEvents = count($events);

            $deletedRows += $this->triggerEventsAndDelete($events);
        } while ($countEvents === $limit);

        $eventTriggerResponseTransfer->setTriggeredRows($triggeredRows);
        $eventTriggerResponseTransfer->setDeletedRows($deletedRows);
        $eventTriggerResponseTransfer->setEventCount($deletedRows);

        $eventTriggerResponseTransfer->setIsSuccessful(true);

        return $eventTriggerResponseTransfer;
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

        $limit = $this->config->getTriggerChunkSize();
        do {
            $events = $this->queryContainer->queryLatestEntityChange($date)->limit($limit)->find()->getData();
            $countEvents = count($events);

            $this->triggerEventsAndDelete($events);
        } while ($countEvents === $limit);
    }

    /**
     * @param string $processId
     * @param int $offset
     * @param int $limit
     *
     * @return array<\Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange>
     */
    protected function getEventEntitiesByProcessId(string $processId, int $limit): array
    {
        $instancePoolingDisabled = false;
        if ($this->isInstancePoolingEnabled()) {
            $this->disableInstancePooling();
            $instancePoolingDisabled = true;
        }

        $events = $this->queryContainer->queryEntityChange($processId)->limit($limit)->find()->getData();

        if ($instancePoolingDisabled) {
            $this->enableInstancePooling();
        }

        if (!$events) {
            return [];
        }

        return $events;
    }

    /**
     * @param array<\Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange> $events
     *
     * @return void
     */
    protected function deleteteEventEntities(array $events): void
    {
        foreach ($events as $event) {
            $this->remove($event);
        }

        $this->commit();
    }

    /**
     * @param array<\Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange> $events
     *
     * @return int
     */
    protected function triggerEventsAndDelete(array $events): int
    {
        if (!$events) {
            return 0;
        }

        $triggeredRows = $this->triggerEvents($events);

        $this->deleteteEventEntities($events);

        return $triggeredRows;
    }

    /**
     * @param array<\Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange> $events
     *
     * @return int
     */
    protected function triggerEvents(array $events): int
    {
        $triggeredRows = 0;
        $eventEntityTransfersByEvent = [];
        foreach ($events as $event) {
            /** @var string $stringData */
            $stringData = $event->getData();
            /** @var array<string, mixed> $data */
            $data = $this->utilEncodingService->decodeJson($stringData, true);
            $id = $data[EventBehavior::EVENT_CHANGE_ENTITY_ID];
            $eventEntityTransfer = new EventEntityTransfer();
            $eventEntityTransfer->setEvent($data[EventBehavior::EVENT_CHANGE_NAME]);
            $eventEntityTransfer->setName($data[EventBehavior::EVENT_CHANGE_ENTITY_NAME]);
            $eventEntityTransfer->setId($id);
            $eventEntityTransfer->setTimestamp($event->getCreatedAt()?->getTimestamp());
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
            $keyId = is_int($id) || is_string($id) ? $id : spl_object_id($eventEntityTransfer);
            $eventEntityTransfersByEvent[$data[EventBehavior::EVENT_CHANGE_NAME]][$keyId] = $eventEntityTransfer;
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
        if (static::$eventBehaviorTableExists === null) {
            static::$eventBehaviorTableExists = class_exists(BaseSpyEventBehaviorEntityChangeQuery::class)
                && class_exists(SpyEventBehaviorEntityChangeQuery::class)
                && $this->propelFacade->tableExists(static::TABLE_NAME_EVENT_BEHAVIOR_ENTITY_CHANGE);
        }

        return static::$eventBehaviorTableExists;
    }
}
