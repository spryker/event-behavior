<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use DateTime;
use Orm\Zed\EventBehavior\Persistence\Base\SpyEventBehaviorEntityChangeQuery as BaseSpyEventBehaviorEntityChangeQuery;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Propel\Runtime\Propel;
use Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use Throwable;

/**
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorPersistenceFactory getFactory()
 */
class EventBehaviorQueryContainer extends AbstractQueryContainer implements EventBehaviorQueryContainerInterface
{
    public const TABLE_EXISTS = 'exists';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $processId
     *
     * @throws \Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryEntityChange($processId)
    {
        $this->eventBehaviorEntityClassExists();

        $query = $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByProcessId($processId)
            ->orderByIdEventBehaviorEntityChange();

        return $query;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \DateTime $date
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryLatestEntityChange(DateTime $date)
    {
        $query = $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByCreatedAt($date, Criteria::LESS_THAN)
            ->orderByIdEventBehaviorEntityChange();

        return $query;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated This method is deprecated without replacement. It's not used by TriggerManager::triggerRuntimeEvents() anymore.
     *
     * @return bool
     */
    public function eventBehaviorTableExists()
    {
        if (
            !class_exists(BaseSpyEventBehaviorEntityChangeQuery::class) ||
            !class_exists(SpyEventBehaviorEntityChangeQuery::class)
        ) {
            return false;
        }

        try {
            $con = Propel::getConnection();
            $sql = "SELECT 1 FROM information_schema.tables WHERE table_name = 'spy_event_behavior_entity_change';";

            /** @var \PDOStatement $stmt */
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt = null;
            $con = null;

            if (!$result) {
                return $result;
            }

            return true;
        } catch (Throwable $t) {
            /*
             *  Any error or exception shows the database
             *  is not ready for transactions.
             */
            return false;
        }
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @throws \Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException
     *
     * @return void
     */
    protected function eventBehaviorEntityClassExists(): void
    {
        if (
            !class_exists(BaseSpyEventBehaviorEntityChangeQuery::class)
            || !class_exists(SpyEventBehaviorEntityChangeQuery::class)
        ) {
            throw new EventBehaviorQueryNotExistsException();
        }
    }
}
