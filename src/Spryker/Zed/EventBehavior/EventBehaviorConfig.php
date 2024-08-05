<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior;

use Spryker\Shared\EventBehavior\EventBehaviorConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class EventBehaviorConfig extends AbstractBundleConfig
{
    /**
     * @var int
     */
    public const EVENT_ENTITY_CHANGE_TIMEOUT_MINUTE = 5;

    /**
     * @var int
     */
    protected const DEFAULT_CHUNK_SIZE = 10000;

    /**
     * @var int
     */
    protected const DEFUALT_TRIGGER_CHUNK_SIZE = 1000;

    /**
     * @var bool
     */
    protected static $isEventDisabled = false;

    /**
     * @api
     *
     * @return int
     */
    public function getEventEntityChangeTimeout()
    {
        return static::EVENT_ENTITY_CHANGE_TIMEOUT_MINUTE;
    }

    /**
     * @api
     *
     * @return bool
     */
    public function getEventBehaviorTriggeringStatus()
    {
        return $this->get(EventBehaviorConstants::EVENT_BEHAVIOR_TRIGGERING_ACTIVE, false);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->get(EventBehaviorConstants::EVENT_BEHAVIOR_CHUNK_SIZE, static::DEFAULT_CHUNK_SIZE);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getTriggerChunkSize(): int
    {
        return $this->get(EventBehaviorConstants::TRIGGER_CHUNK_SIZE, static::DEFUALT_TRIGGER_CHUNK_SIZE);
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isInstancePoolingEnabled(): bool
    {
        return $this->get(EventBehaviorConstants::ENABLE_INSTANCE_POOLING, true);
    }

    /**
     * @api
     *
     * @return bool
     */
    public static function disableEvent()
    {
        return static::$isEventDisabled = true;
    }

    /**
     * @api
     *
     * @return bool
     */
    public static function enableEvent()
    {
        return static::$isEventDisabled = false;
    }

    /**
     * @api
     *
     * @return bool
     */
    public static function isEventBehaviorDisabled()
    {
        return static::$isEventDisabled;
    }

    /**
     * Specification:
     * - Recommended maximum data size for event messages in KB.
     * - Used to log a warning if the event message data size exceeds this limit.
     *
     * @api
     *
     * @return int
     */
    public function getMaxRecommendedEventMessageDataSize(): int
    {
        return $this->get(EventBehaviorConstants::MAX_RECOMMENDED_EVENT_MESSAGE_DATA_SIZE, 256);
    }
}
