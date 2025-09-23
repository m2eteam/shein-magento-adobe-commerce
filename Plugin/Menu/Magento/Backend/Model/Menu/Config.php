<?php

declare(strict_types=1);

namespace M2E\Shein\Plugin\Menu\Magento\Backend\Model\Menu;

class Config
{
    private bool $isProcessed = false;
    private \M2E\Shein\Model\Module $module;

    public function __construct(\M2E\Shein\Model\Module $module)
    {
        $this->module = $module;
    }

    public function afterGetMenu(\Magento\Backend\Model\Menu\Config $interceptor, \Magento\Backend\Model\Menu $result)
    {
        if ($this->isProcessed) {
            return $result;
        }

        $this->isProcessed = true;

        if (!$this->module->isModuleConfigured()) {
            $sheinItem = $result->get('M2E_Shein::shein');
            if ($sheinItem) {
                $sheinItem->setAction('m2e_shein/dashboard/welcome/');
            }
        }

        return $result;
    }
}
