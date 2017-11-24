<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
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
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Customer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Nosto\Tagging\Api\CustomerRepositoryInterface;
use Nosto\Tagging\Api\Data\CustomerInterface;
use Nosto\Tagging\Model\AbstractBaseRepository;
use Nosto\Tagging\Model\RepositoryTrait;
use Nosto\Tagging\Model\ResourceModel\Customer as CustomerResource;
use Nosto\Tagging\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class Repository extends AbstractBaseRepository implements CustomerRepositoryInterface
{
    private $searchCriteriaBuilder;

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

        parent::__construct(
            $customerResource
        );

        $this->setObjectSearchResultsFactory($customerSearchResultsFactory);
        $this->setObjectCollectionFactory($customerCollectionFactory);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
    /**
     * @inheritdoc
     */
    public function save(CustomerInterface $customer)
    {
        $this->getObjectResource()->save($customer);

        return $customer;
    }

    public function getIdentityKey()
    {
        return CustomerInterface::CUSTOMER_ID;
    }

    /**
     * @inheritdoc
     */
    public function getOneByNostoIdAndQuoteId($nostoId, $quoteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CustomerInterface::NOSTO_ID, $nostoId, 'eq')
            ->addFilter(CustomerInterface::QUOTE_ID, $quoteId, 'eq')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $items = $this->search($searchCriteria)->getItems();
        foreach ($items as $customer) {

            return $customer;
        }

        return null;
    }

}
