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

namespace Nosto\Tagging\Observer\Adminhtml;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\Store;
use Nosto\Tagging\Helper\Account as NostoAccountHelper;
use Nosto\Tagging\Helper\Data as NostoHelperData;
use Nosto\Tagging\Helper\Scope as NostoHelperScope;
use Nosto\Tagging\Logger\Logger as NostoLogger;
use Nosto\Tagging\Model\Indexer\QueueIndexer;

/**
 * Observer to mark all indexed products as dirty if settings have changed
 */
class Config implements ObserverInterface
{
    public const WEBSITE_SCOPE_KEY = 'website';
    public const STORE_SCOPE_KEY = 'store';

    /** @var NostoLogger  */
    private NostoLogger $logger;

    /** @var ModuleManager  */
    private ModuleManager $moduleManager;

    /** @var NostoHelperScope  */
    private NostoHelperScope $nostoHelperScope;

    /** @var NostoAccountHelper  */
    private NostoAccountHelper $nostoAccountHelper;

    /** var QueueIndexer */
    private QueueIndexer $queueIndexer;

    /** @var IndexerRegistry */
    private IndexerRegistry $indexerRegistry;

    /**
     * Config Constructor.
     *
     * @param NostoLogger $logger
     * @param ModuleManager $moduleManager
     * @param NostoHelperScope $nostoHelperScope
     * @param NostoAccountHelper $nostoAccountHelper
     * @param IndexerRegistry $indexerRegistry
     * @param QueueIndexer $queueIndexer
     */
    public function __construct(
        NostoLogger $logger,
        ModuleManager $moduleManager,
        NostoHelperScope $nostoHelperScope,
        NostoAccountHelper $nostoAccountHelper,
        IndexerRegistry $indexerRegistry,
        QueueIndexer $queueIndexer
    ) {
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
        $this->nostoHelperScope = $nostoHelperScope;
        $this->nostoAccountHelper = $nostoAccountHelper;
        $this->queueIndexer = $queueIndexer;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Observer method to mark all indexed products as dirty on the index table
     *
     * @param Observer $observer the dispatched event
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $changedConfig = $observer->getData('changed_paths');
        // If array of changes contains only indexer allow memory, we can skip
        if (empty($changedConfig)
            || !$this->moduleManager->isEnabled(NostoHelperData::MODULE_NAME)
            || (count($changedConfig) === 1 && $changedConfig[0] === NostoHelperData::XML_PATH_INDEXER_MEMORY)
        ) {
            return;
        }
        $storeRequest = $observer->getData(self::STORE_SCOPE_KEY);
        $websiteRequest = $observer->getData(self::WEBSITE_SCOPE_KEY);
        // If $storeRequest && $websiteRequest are empty strings, means we're in a global scope.
        // Mark as dirty for all stores if config is different than the one just saved
        if (empty($storeRequest) && empty($websiteRequest)) { // Global scope
            $stores = $this->nostoHelperScope->getStores();
            foreach ($stores as $store) {
                $this->reindexAll($store);
            }
        } elseif (!empty($websiteRequest) && empty($storeRequest)) { // Website Level
            // Get stores from the website and mark them all as dirty
            $website = $this->nostoHelperScope->getWebsite($websiteRequest);
            $stores = $website->getStores();
            foreach ($stores as $store) {
                $this->reindexAll($store);
            }
        } else { // Store View Level
            $store = $this->nostoHelperScope->getStore($storeRequest);
            $this->reindexAll($store);
        }
    }

    /**
     * Wrapper to log and mark all products as dirty after configuration has changed
     * @param Store $store
     */
    private function reindexAll(Store $store)
    {
        if ($this->nostoAccountHelper->nostoInstalledAndEnabled($store)) {
            $this->logger->infoWithSource(
                sprintf(
                    'Nosto Settings updated, marking all indexed products as dirty for store %s',
                    $store->getName()
                ),
                ['storeId' => $store->getId()],
                $this
            );

            $indexer = $this->indexerRegistry->get(QueueIndexer::INDEXER_ID);
            if (!$indexer->isScheduled()) {
                $this->logger->infoWithSource(
                    'Not performing full Nosto reindex as the indexer is not scheduled',
                    ['storeId' => $store->getId()],
                    $this
                );
            } else {
                try {
                    $this->queueIndexer->doIndex($store);
                } catch (Exception $e) {
                    $this->logger->exception($e);
                }
            }
        }
    }
}
