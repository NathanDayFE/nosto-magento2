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

namespace Nosto\Tagging\Model\Customer;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Nosto\Tagging\Api\CustomerRepositoryInterface;
use Nosto\Tagging\Api\Data\CustomerInterface;
use Nosto\Tagging\Model\Customer\Customer as NostoCustomer;
use Nosto\Tagging\Model\ResourceModel\Customer as CustomerResource;
use Nosto\Tagging\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Nosto\Tagging\Util\Repository as RepositoryUtil;

class Repository implements CustomerRepositoryInterface
{
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CustomerCollectionFactory $customerCollectionFactory;
    private CustomerSearchResultsFactory $customerSearchResultsFactory;
    private CustomerResource $customerResource;

    /**
     * Customer repository constructor
     *
     * @param CustomerResource $customerResource
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerSearchResultsFactory $customerSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomerResource $customerResource,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerSearchResultsFactory $customerSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerSearchResultsFactory = $customerSearchResultsFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerResource = $customerResource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Save Queue entry
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerInterface
     * @throws Exception
     * @throws AlreadyExistsException
     *
     * @suppress PhanTypeMismatchArgument
     */
    public function save(CustomerInterface $customer)
    {
        /** @noinspection PhpParamsInspection */
        $this->customerResource->save($customer);

        return $customer;
    }

    /**
     * Get customer entry by nosto id and quote id. If multiple entries
     * are found first one will be returned.
     *
     * @param string $nostoId
     * @param int $quoteId
     *
     * @return CustomerInterface|null
     */
    public function getOneByNostoIdAndQuoteId(string $nostoId, int $quoteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CustomerInterface::NOSTO_ID, $nostoId)
            ->addFilter(CustomerInterface::QUOTE_ID, $quoteId)
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        /** @var CustomerInterface[]|null $items */
        $items = $this->search($searchCriteria)->getItems();
        /** @var CustomerInterface|null $item */
        $item = $items ? reset($items) : null;
        return $item;
    }

    /**
     * Get customer entry by field name and quote id. If multiple entries
     * are found first one will be returned.
     *
     * @param int $quoteId
     *
     * @return CustomerInterface|null
     */
    public function getOneByQuoteId(int $quoteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(NostoCustomer::QUOTE_ID, $quoteId)
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        /** @var CustomerInterface[]|null $items */
        $items = $this->search($searchCriteria)->getItems();
        /** @var CustomerInterface|null $item */
        $item = $items ? reset($items) : null;
        return $item;
    }

    /**
     * Get customer entry by restore cart hash. If multiple entries
     * are found first one will be returned.
     *
     * @param string $hash
     *
     * @return CustomerInterface|null
     */
    public function getOneByRestoreCartHash(string $hash)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CustomerInterface::RESTORE_CART_HASH, $hash)
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        /** @var CustomerInterface[]|null $items */
        $items = $this->search($searchCriteria)->getItems();
        /** @var CustomerInterface|null $item */
        $item = $items ? reset($items) : null;
        return $item;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return CustomerSearchResults
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->customerCollectionFactory->create();
        $searchResults = $this->customerSearchResultsFactory->create();

        /**
         * Returning \Magento\Framework\Api\Search\SearchResult
         * but declared to return CustomerSearchResults
         */
        /** @phan-suppress-next-next-line PhanTypeMismatchReturnSuperType */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new RepositoryUtil())->search(
            $collection,
            $searchCriteria,
            $searchResults
        );
    }
}
