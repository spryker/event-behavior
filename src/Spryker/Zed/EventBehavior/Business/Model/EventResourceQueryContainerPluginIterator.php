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
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface $plugin
     * @param int $chunkSize
     */
    public function __construct(EventResourceQueryContainerPluginInterface $plugin, int $chunkSize)
    {
        parent::__construct($plugin, $chunkSize);
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = $this->plugin->queryData()
            ->offset($this->offset)
            ->limit($this->chunkSize)
            ->where($this->plugin->getIdColumnName() . ModelCriteria::ISNOTNULL)
            ->select([$this->plugin->getIdColumnName()])
            ->orderBy($this->plugin->getIdColumnName())
            ->find()
            ->getData();
    }
}
