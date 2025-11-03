<?php

declare(strict_types=1);

namespace M2E\Shein\Model;

class Module
{
    private const CLOUD_BASE_URL = 'https://shein.m2e.cloud/';
    private const CLOUD_PATH_PATTERN = '?magento2_embedded=true&domain=%s&signature=%s';

    private const INSTALLED_FLAG_CONFIG_PATH = 'm2e/shein/installed';
    private const INIT_HOST_CONFIG_PATH = 'm2e/shein/init_host';

    private \Magento\Framework\App\Config\ScopeConfigInterface $config;
    private \Magento\Framework\App\Config\Storage\WriterInterface $configWriter;
    private \M2E\M2ECloudMagentoConnector\Model\IntegrationFactory $integrationFactory;
    private CloudProcessor $cloudProcessor;
    private \Magento\Framework\App\CacheInterface $appCache;
    private \M2E\Shein\Helper\Module $moduleHelper;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \M2E\M2ECloudMagentoConnector\Model\IntegrationFactory $integrationFactory,
        \M2E\Shein\Model\CloudProcessor $salesChannelProcessor,
        \Magento\Framework\App\CacheInterface $appCache,
        \M2E\Shein\Helper\Module $moduleHelper
    ) {
        $this->config = $config;
        $this->configWriter = $configWriter;
        $this->integrationFactory = $integrationFactory;
        $this->cloudProcessor = $salesChannelProcessor;
        $this->appCache = $appCache;
        $this->moduleHelper = $moduleHelper;
    }

    public function isModuleConfigured(): bool
    {
        return $this->isSameHost() && $this->config->isSetFlag(self::INSTALLED_FLAG_CONFIG_PATH);
    }

    public function activate(): void
    {
        $integration = $this->getIntegration();
        $integration->activate();

        $this->cloudProcessor->init();

        $this->setModuleAsConfigured();

        $this->appCache->clean([\Magento\Backend\Block\Menu::CACHE_TAGS, 'CONFIG']);
    }

    public function getM2eCloudUrl(): string
    {
        return sprintf(
            $this->getM2eCloudBaseUrl() . self::CLOUD_PATH_PATTERN,
            $this->moduleHelper->getDomain(),
            $this->getSignature()
        );
    }

    public function getM2eCloudBaseUrl(): string
    {
        return self::CLOUD_BASE_URL;
    }

    private function getSignature(): string
    {
        $integration = $this->getIntegration();

        return hash_hmac(
            'sha256',
            $integration->getConsumerKey(),
            $integration->getConsumerSecret()
        );
    }

    private function setModuleAsConfigured(): void
    {
        $this->configWriter->save(self::INSTALLED_FLAG_CONFIG_PATH, 1);
        $this->configWriter->save(self::INIT_HOST_CONFIG_PATH, $this->moduleHelper->getDomain());
    }

    private function getIntegration(): \M2E\M2ECloudMagentoConnector\Model\Integration
    {
        return $this->integrationFactory->create();
    }

    private function isSameHost(): bool
    {
        $hostDomain = $this->moduleHelper->getDomain();
        $initDomain = $this->config->getValue(self::INIT_HOST_CONFIG_PATH);

        return $hostDomain === $initDomain;
    }
}
