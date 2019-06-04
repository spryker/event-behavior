<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Iterator;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

class EventResourceRepositoryPluginIterator extends AbstractEventResourcePluginIterator implements Iterator
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface
     */
    protected $plugin;

    /**
     * @var \Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer[]
     */
    protected $data;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param int $chunkSize
     */
    public function __construct(EventResourceRepositoryPluginInterface $plugin, int $chunkSize)
    {
        parent::__construct($plugin, $chunkSize);

        $this->data = $plugin->getData();
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = array_slice($offset, $this->offset, $this->chunkSize, true);
    }
}
