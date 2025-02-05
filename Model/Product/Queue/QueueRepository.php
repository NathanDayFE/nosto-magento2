<?php
/**
 * Copyright (c) 2020, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2020 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Product\Queue;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Nosto\Tagging\Api\Data\ProductUpdateQueueInterface;
use Nosto\Tagging\Api\ProductUpdateQueueRepositoryInterface;
use Nosto\Tagging\Model\ResourceModel\Product\Update\Queue as QueueResource;
use Nosto\Tagging\Model\ResourceModel\Product\Update\Queue\QueueCollection;
use Nosto\Tagging\Model\ResourceModel\Product\Update\Queue\QueueCollectionFactory;

class QueueRepository implements ProductUpdateQueueRepositoryInterface
{
    /** @var QueueCollectionFactory  */
    private QueueCollectionFactory $queueCollectionFactory;

    /** @var QueueResource  */
    private QueueResource $queueResource;

    /**
     * IndexRepository constructor.
     *
     * @param QueueResource $queueResource
     * @param QueueCollectionFactory $queueCollectionFactory
     */
    public function __construct(
        QueueResource $queueResource,
        QueueCollectionFactory $queueCollectionFactory
    ) {
        $this->queueResource = $queueResource;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    public function getTotalCount(Store $store)
    {
        $collection = $this->queueCollectionFactory->create();
        if ((int)$store->getId() !== 0) {
            $collection->addStoreFilter($store);
        }
        return $collection->getSize();
    }

    /**
     * @param StoreInterface $store
     * @return QueueCollection
     */
    public function getByStore(StoreInterface $store)
    {
        /* @var QueueCollection $collection */
        return $this->queueCollectionFactory->create()
            ->addStoreFilter($store);
    }

    /**
     * @param ProductUpdateQueueInterface $entry
     * @return ProductUpdateQueueInterface|QueueResource
     * @throws AlreadyExistsException
     * @noinspection PhpParamsInspection
     */
    public function save(ProductUpdateQueueInterface $entry)
    {
        /** @phan-suppress-next-line PhanTypeMismatchArgument */
        return $this->queueResource->save($entry);
    }

    /**
     * @param ProductUpdateQueueInterface $entry
     * @throws Exception
     * @noinspection PhpParamsInspection
     */
    public function delete(ProductUpdateQueueInterface $entry)
    {
        /** @phan-suppress-next-line PhanTypeMismatchArgument */
        $this->queueResource->delete($entry);
    }

    /**
     * @param ProductUpdateQueueInterface $entry
     * @return bool
     */
    public function isEntryDuplicated(ProductUpdateQueueInterface $entry): bool
    {
        $collection = $this->queueCollectionFactory->create()
            ->addStoreIdFilter($entry->getStoreId())
            ->addStatusFilter($entry->getStatus())
            ->addActionFilter($entry->getAction())
            ->limitResults(1);
        return (bool)$collection->getItems();
    }
}
