<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

interface EventResourcePluginResolverInterface
{
    /**
     * @param array<string> $resources
     * @param array<(string|int)> $ids
     * @param array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface> $resourcePublisherPlugins
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = [], array $resourcePublisherPlugins = []): void;

    /**
     * @param array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface> $resourcePublisherPlugins
     *
     * @return array<string>
     */
    public function getAvailableResourceNames(array $resourcePublisherPlugins = []): array;
}
