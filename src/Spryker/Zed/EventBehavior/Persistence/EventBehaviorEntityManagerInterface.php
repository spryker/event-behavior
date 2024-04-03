<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

interface EventBehaviorEntityManagerInterface
{
    /**
     * @param array<int> $primaryKeys
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByPrimaryKeys(array $primaryKeys = []): int;

    /**
     * @param string $processId
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByProcessId(string $processId): int;
}
