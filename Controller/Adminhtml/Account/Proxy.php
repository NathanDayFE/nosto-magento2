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

namespace Nosto\Tagging\Controller\Adminhtml\Account;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\Result\Redirect;

class Proxy extends Base
{
    public const ADMIN_RESOURCE = 'Nosto_Tagging::system_nosto_account';

    /**
     * @var Session
     */
    private Session $backendAuthSession;

    /**
     * @param Context $context
     * @param Session $backendAuthSession
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession
    ) {
        parent::__construct($context);

        $this->_publicActions = ['proxy'];
        $this->backendAuthSession = $backendAuthSession;
    }

    /**
     * Action that acts as a proxy to the account/index page, when the frontend
     * oauth controller redirects the admin user back to the backend after
     * finishing the oauth authorization cycle.
     * This is a workaround as you cannot redirect directly to a protected
     * action in the backend end from the front end. The action also handles
     * passing along any error/success messages.
     * @return Redirect
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('message_type');
        $code = $this->getRequest()->getParam('message_code');
        $text = $this->getRequest()->getParam('message_text');
        if ($type !== null && $code !== null) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->backendAuthSession->setData(
                'nosto_message',
                [
                    'message_type' => $type,
                    'message_code' => $code,
                    'message_text' => $text,
                ]
            );
        }

        if (($storeId = (int)$this->getRequest()->getParam('store')) !== 0) {
            return $this->resultRedirectFactory->create()
                ->setPath('*/*/index', ['store' => $storeId]);
        }

        return $this->resultRedirectFactory->create()
            ->setPath('*/*/index', []);
    }
}
