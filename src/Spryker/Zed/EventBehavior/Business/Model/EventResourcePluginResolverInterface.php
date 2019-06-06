<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

interface EventResourcePluginResolverInterface
{
    /**
     * @param string[] $resources
     * @param int[] $ids
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids): void;

    /**
     * @return string[]
     */
    public function getAvailableResourceNames(): array;
}
