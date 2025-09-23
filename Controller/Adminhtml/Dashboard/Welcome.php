<?php

declare(strict_types=1);

namespace M2E\Shein\Controller\Adminhtml\Dashboard;

class Welcome extends AbstractController implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    private \Magento\Framework\View\Result\PageFactory $resultPageFactory;
    private \M2E\M2ECloudMagentoConnector\Model\IntegrationFactory $integrationFactory;
    private \M2E\Shein\Model\Module $module;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context,
        \M2E\M2ECloudMagentoConnector\Model\IntegrationFactory $integrationFactory,
        \M2E\Shein\Model\Module $module
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->integrationFactory = $integrationFactory;
        $this->module = $module;

        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->module->isModuleConfigured()) {
            $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $result->setPath('m2e_shein/dashboard/index');
        } else {
            $this->initM2eIntegration();

            $result = $this->resultPageFactory->create();
            $result->setActiveMenu(self::MENU_ID);
            $result->getConfig()
                   ->getTitle()
                   ->set(__('Welcome to M2E Shein Connect!'));
        }

        return $result;
    }

    private function initM2eIntegration(): void
    {
        $integration = $this->integrationFactory->create();
        $integration->prepareForInstallation();
    }
}
