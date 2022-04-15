<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;

class EventResourceQueryContainerPluginIterator extends AbstractEventResourcePluginIterator
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface
     */
    protected $plugin;

    /**
     * @var array<int>
     */
    protected $ids;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param int $chunkSize
     * @param array<int> $ids
     */
    public function __construct(EventResourceQueryContainerPluginInterface $plugin, int $chunkSize, $ids = [])
    {
        parent::__construct($plugin, $chunkSize);

        $this->ids = $ids;
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = $this->plugin->queryData($this->ids)
            ->offset($this->offset)
            ->limit($this->chunkSize)
            ->orderBy((string)$this->plugin->getIdColumnName())
            ->find()
            ->getData();
    }
}
