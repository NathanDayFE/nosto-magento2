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

namespace Nosto\Tagging\Observer\Product;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Module\Manager as ModuleManager;
use Nosto\Tagging\Helper\Data as NostoHelperData;
use Nosto\Tagging\Model\Indexer\QueueIndexer as QueueIndexer;

abstract class Base implements ObserverInterface
{
    /** @var ModuleManager $moduleManager */
    public ModuleManager $moduleManager;

    /** @var ProductRepository $productRepository */
    public ProductRepository $productRepository;

    /** @var NostoHelperData $dataHelper */
    public NostoHelperData $dataHelper;

    /** @var IndexerInterface */
    public IndexerInterface $indexer;

    /** @var QueueIndexer $queueIndexer */
    public QueueIndexer $queueIndexer;

    /**
     * Base constructor.
     * @param ModuleManager $moduleManager
     * @param ProductRepository $productRepository
     * @param NostoHelperData $dataHelper
     * @param IndexerRegistry $indexerRegistry
     * @param QueueIndexer $indexerInvalidate
     */
    public function __construct(
        ModuleManager $moduleManager,
        ProductRepository $productRepository,
        NostoHelperData $dataHelper,
        IndexerRegistry $indexerRegistry,
        QueueIndexer $indexerInvalidate
    ) {
        $this->moduleManager = $moduleManager;
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->indexer = $indexerRegistry->get(QueueIndexer::INDEXER_ID);
        $this->queueIndexer = $indexerInvalidate;
    }

    /**
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isEnabled(NostoHelperData::MODULE_NAME)
            && !$this->indexer->isScheduled()
        ) {
            /* @var Product $product */
            $product = $this->extractProduct($observer);

            if ($product instanceof Product && $product->getId()) {
                $this->queueIndexer->executeRow($product->getId());
            }
        }
    }

    /**
     * Default method for extracting product from the observer
     * @param Observer $observer
     * @return mixed
     */
    public function extractProduct(Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $observer->getProduct();
    }
}
