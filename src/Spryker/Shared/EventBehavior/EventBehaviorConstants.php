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
     */
    public const EVENT_BEHAVIOR_TRIGGERING_ACTIVE = 'EVENT_BEHAVIOR_TRIGGERING_ACTIVE';
}
