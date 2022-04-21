<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;

/**
 * @extends \Spryker\Zed\EventBehavior\Business\Model\AbstractEventResourcePluginIterator<array<\Generated\Shared\Transfer\EventEntityTransfer>>
 */
class EventResourceRepositoryBulkPluginIterator extends AbstractEventResourcePluginIterator
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface
     */
    protected $plugin;

    /**
     * @var array<int>
     */
    protected $ids;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param int $chunkSize
     * @param array<int> $ids
     */
    public function __construct(EventResourceBulkRepositoryPluginInterface $plugin, int $chunkSize, array $ids)
    {
        parent::__construct($plugin, $chunkSize);
        $this->ids = $ids;
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = $this->plugin->getData($this->offset, $this->chunkSize);
    }
}
