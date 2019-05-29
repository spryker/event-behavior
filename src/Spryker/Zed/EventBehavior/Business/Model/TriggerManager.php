<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use DateInterval;
use DateTime;
use Generated\Shared\Transfer\EventEntityTransfer;
use Propel\Runtime\Connection\Exception\ConnectionException;
use Propel\Runtime\Exception\PropelException;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface;
use Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException;
use Spryker\Zed\EventBehavior\Persistence\Propel\Behavior\EventBehavior;
use Spryker\Zed\Kernel\RequestIdentifier;

class TriggerManager implements TriggerManagerInterface
{
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
     * @var bool|null
     */
    protected static $eventBehaviorTableExists;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface $utilEncodingService
     * @param \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface $queryContainer
     * @param \Spryker\Zed\EventBehavior\EventBehaviorConfig $config
     */
    public function __construct(EventBehaviorToEventInterface $eventFacade, EventBehaviorToUtilEncodingInterface $utilEncodingService, EventBehaviorQueryContainerInterface $queryContainer, EventBehaviorConfig $config)
    {
        $this->eventFacade = $eventFacade;
        $this->utilEncodingService = $utilEncodingService;
        $this->queryContainer = $queryContainer;
        $this->config = $config;
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
        try {
            $events = $this->queryContainer->queryEntityChange($processId)->find()->getData();
            static::$eventBehaviorTableExists = true;
        } catch (PropelException | ConnectionException | EventBehaviorQueryNotExistsException $e) {
            static::$eventBehaviorTableExists = false;

            return;
        }

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
            $this->eventFacade->trigger($data[EventBehavior::EVENT_CHANGE_NAME], $eventEntityTransfer);
            $triggeredRows++;
        }

        return $triggeredRows;
    }
}
