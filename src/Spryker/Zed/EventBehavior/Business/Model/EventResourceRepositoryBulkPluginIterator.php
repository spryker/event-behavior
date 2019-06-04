<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Iterator;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface;

class EventResourceRepositoryBulkPluginIterator extends AbstractEventResourcePluginIterator implements Iterator
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface
     */
    protected $plugin;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface $plugin
     * @param int $chunkSize
     */
    public function __construct(EventResourceBulkRepositoryPluginInterface $plugin, int $chunkSize)
    {
        parent::__construct($plugin, $chunkSize);
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = $this->plugin->getData($this->offset, $this->chunkSize);
    }
}
