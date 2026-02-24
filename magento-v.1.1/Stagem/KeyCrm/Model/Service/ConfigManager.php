<?php

namespace Stagem\KeyCrm\Model\Service;

class ConfigManager implements \Stagem\KeyCrm\Api\ConfigManagerInterface
{
    private $config;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function getConfigValue($path)
    {
        return $this->config->getValue($path);
    }
}
