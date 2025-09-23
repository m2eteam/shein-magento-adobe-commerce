<?php

declare(strict_types=1);

namespace M2E\Shein\Controller\Adminhtml\Dashboard;

abstract class AbstractController extends \Magento\Backend\App\Action
{
    public const MENU_ID = 'M2E_Shein::shein';

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Shein::dashboard');
    }
}
