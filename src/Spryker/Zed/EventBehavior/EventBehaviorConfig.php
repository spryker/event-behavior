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
    public const EVENT_ENTITY_CHANGE_TIMEOUT_MINUTE = 5;
    protected const DEFAULT_CHUNK_SIZE = 10000;
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
        return $this->get(EventBehaviorConstants::EVENT_TRIGGER_CHUNK_SIZE, static::DEFUALT_TRIGGER_CHUNK_SIZE);
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
}
