<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\EventBehavior;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface EventBehaviorConstants
{
    /**
     * Specification:
     * - Is triggering activated for behavior events (true|false)
     *
     * @api
     *
     * @var string
     */
    public const EVENT_BEHAVIOR_TRIGGERING_ACTIVE = 'EVENT_BEHAVIOR_TRIGGERING_ACTIVE';

    /**
     * Specification:
     * - Chunk size for behavior events.
     *
     * @api
     *
     * @var string
     */
    public const EVENT_BEHAVIOR_CHUNK_SIZE = 'EVENT_BEHAVIOR_CHUNK_SIZE';

    /**
     * Specification:
     * - Chunk size for trigger events.
     *
     * @api
     *
     * @var string
     */
    public const TRIGGER_CHUNK_SIZE = 'EVENT_BEHAVIOR:TRIGGER_CHUNK_SIZE';

    /**
     * Specification:
     * - Is instance pooling enabled for event triggering.
     *
     * @api
     *
     * @var string
     */
    public const ENABLE_INSTANCE_POOLING = 'EVENT_BEHAVIOR:ENABLE_INSTANCE_POOLING';

    /**
     * Specification:
     * - Recommended maximum data size for event messages in KB.
     * - Used to log a warning if the event message data size exceeds this limit.
     *
     * @api
     *
     * @var string
     */
    public const MAX_RECOMMENDED_EVENT_MESSAGE_DATA_SIZE = 'MAX_RECOMMENDED_EVENT_MESSAGE_DATA_SIZE';
}
