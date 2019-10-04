<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence\Propel;

use Orm\Zed\EventBehavior\Persistence\Base\SpyEventBehaviorEntityChange as BaseSpyEventBehaviorEntityChange;
use Propel\Runtime\Connection\ConnectionInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;

/**
 * Skeleton subclass for representing a row from the 'spy_event_behavior_entity_change' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 */
abstract class AbstractSpyEventBehaviorEntityChange extends BaseSpyEventBehaviorEntityChange
{
    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface|null $con
     *
     * @return void
     */
    public function save(?ConnectionInterface $con = null)
    {
        if ($this->isEventDisabled()) {
            return;
        }

        parent::save($con);
    }

    /**
     * @return bool
     */
    protected function isEventDisabled()
    {
        return EventBehaviorConfig::isEventBehaviorDisabled();
    }
}
