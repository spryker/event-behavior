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
     * @param array $resourcePublisherPlugins
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = [], array $resourcePublisherPlugins = []): void;

    /**
     * @param array $resourcePublisherPlugins
     *
     * @return string[]
     */
    public function getAvailableResourceNames(array $resourcePublisherPlugins = []): array;
}
