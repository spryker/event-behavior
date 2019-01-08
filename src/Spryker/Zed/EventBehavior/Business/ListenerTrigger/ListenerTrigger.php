<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\ListenerTrigger;

use Generated\Shared\Transfer\EventEntityTransfer;
use InvalidArgumentException;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;

class ListenerTrigger implements ListenerTriggerInterface
{
    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    protected $eventFacade;

    /**
     * @var \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface $eventFacade
     * @param \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface $utilEncodingService
     */
    public function __construct(
        EventBehaviorToEventInterface $eventFacade,
        EventBehaviorToUtilEncodingInterface $utilEncodingService
    ) {
        $this->eventFacade = $eventFacade;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param string $eventListenerName
     * @param string $transferData
     * @param string $format
     *
     * @return void
     */
    public function triggerEventListenerByName(string $eventListenerName, string $transferData, string $format): void
    {
        $eventEntityTransfers = $this->getEventEntityTransfers($transferData, $format);

        $this->eventFacade->triggerByListenerName($eventListenerName, $eventEntityTransfers);
    }

    /**
     * @param string $transferData
     * @param string $format
     *
     * @throws \InvalidArgumentException
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
     */
    protected function getEventEntityTransfers(string $transferData, string $format): array
    {
        $decodedTransferData = $this->utilEncodingService->decodeFromFormat($transferData, $format);

        if (!$decodedTransferData) {
            throw new InvalidArgumentException('Given transfer data is invalid.');
        }

        if ($this->isSingleTransferData($decodedTransferData)) {
            return [$this->getEventEntityTransfer($decodedTransferData)];
        }

        $eventEntityTransfers = [];
        foreach ($decodedTransferData as $decodedTransferDatum) {
            $eventEntityTransfers[] = $this->getEventEntityTransfer($decodedTransferDatum);
        }

        return $eventEntityTransfers;
    }

    /**
     * @param array $data
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer
     */
    protected function getEventEntityTransfer(array $data): EventEntityTransfer
    {
        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->fromArray($data);

        return $eventEntityTransfer;
    }

    /**
     * @param array $transferData
     *
     * @return bool
     */
    protected function isSingleTransferData(array $transferData): bool
    {
        return $this->isAssociative($transferData);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isAssociative(array $data): bool
    {
        foreach (array_keys($data) as $key) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }
}
