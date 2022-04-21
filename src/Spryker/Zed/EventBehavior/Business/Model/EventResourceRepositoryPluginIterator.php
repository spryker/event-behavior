<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface;

/**
 * @extends \Spryker\Zed\EventBehavior\Business\Model\AbstractEventResourcePluginIterator<array<\Generated\Shared\Transfer\EventEntityTransfer>>
 */
class EventResourceRepositoryPluginIterator extends AbstractEventResourcePluginIterator
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface
     */
    protected $plugin;

    /**
     * @var array<\Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer>
     */
    protected $data;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceRepositoryPluginInterface $plugin
     * @param int $chunkSize
     * @param array<int> $ids
     */
    public function __construct(EventResourceRepositoryPluginInterface $plugin, int $chunkSize, array $ids = [])
    {
        parent::__construct($plugin, $chunkSize);

        $this->data = $plugin->getData($ids);
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = array_slice($this->data, $this->offset, $this->chunkSize, true);
    }
}
