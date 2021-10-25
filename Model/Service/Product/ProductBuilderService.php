<?php
/**
 * Copyright (c) 2021, Nosto Solutions Ltd
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
 * @copyright 2021 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Service\Product;

use Nosto\Tagging\Helper\Account as NostoAccountHelper;
use Nosto\Tagging\Helper\Data as NostoDataHelper;
use Nosto\Tagging\Logger\Logger as NostoLogger;
use Nosto\Tagging\Model\Service\AbstractService;
use Nosto\Tagging\Model\Service\Product\Builder\ProductAvailabilityService;
use Nosto\Tagging\Model\Service\Product\Builder\ProductImageBuilderService;
use Nosto\Tagging\Model\Service\Product\Attribute\AttributeServiceInterface;

class ProductBuilderService extends AbstractService
{

    /** @var AttributeServiceInterface */
    private $attributeService;

    /** @var ProductAvailabilityService */
    private $productAvailabilityService;

    /** @var ProductImageBuilderService */
    private $productImageBuilderService;

    /**
     * ProductBuilderService constructor.
     * @param NostoLogger $logger
     * @param NostoDataHelper $nostoDataHelper
     * @param NostoAccountHelper $nostoAccountHelper
     * @param AttributeServiceInterface $attributeService
     * @param ProductAvailabilityService $productAvailabilityService
     * @param ProductImageBuilderService $productImageBuilderService
     */
    public function __construct(
        NostoLogger $logger,
        NostoDataHelper $nostoDataHelper,
        NostoAccountHelper $nostoAccountHelper,
        AttributeServiceInterface $attributeService,
        ProductAvailabilityService $productAvailabilityService,
        ProductImageBuilderService $productImageBuilderService
    ) {
        parent::__construct($nostoDataHelper, $nostoAccountHelper, $logger);
        $this->attributeService = $attributeService;
        $this->productAvailabilityService = $productAvailabilityService;
        $this->productImageBuilderService = $productImageBuilderService;
    }

    /**
     * @return AttributeServiceInterface
     */
    public function getAttributeService()
    {
        return $this->attributeService;
    }

    /**
     * @return ProductAvailabilityService
     */
    public function getProductAvailabilityService()
    {
        return $this->productAvailabilityService;
    }

    /**
     * @return ProductAvailabilityService
     */
    public function getProductImageBuilderService()
    {
        return $this->productImageBuilderService;
    }

}
