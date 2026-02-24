<?php

namespace Stagem\KeyCrm\Helper;

use Stagem\KeyCrm\ApiClient\ApiClientFactory;
use Stagem\KeyCrm\ApiClient\Exceptions\CurlException;
use Stagem\KeyCrm\ApiClient\Exceptions\FailedResponseException;
use Stagem\KeyCrm\ApiClient\Exceptions\InvalidJsonException;
use Stagem\KeyCrm\Model\Logger\Logger;
use Stagem\KeyCrm\Model\Service\ConfigManager;

class Proxy
{
    private Logger $logger;
    private ?ApiClientFactory $apiClient = null; // Use ?ApiClientFactory for possible null value
    private string $url;
    private ?string $apiKey;

    /**
     * Proxy constructor.
     *
     * @param string $pathUrl
     * @param string $pathKey
     * @param ConfigManager $config
     * @param Logger $logger
     */
    public function __construct(
        string        $pathUrl,
        string        $pathKey,
        ConfigManager $config,
        Logger        $logger
    )
    {
        $this->logger = $logger;
        $this->url = $config->getConfigValue($pathUrl);
        $this->apiKey = $config->getConfigValue($pathKey);

        if ($this->isConfigured()) {
            $this->init();
        }
    }

    /**
     * Magic method to forward calls to the API client.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed|false
     */
    public function __call(string $method, array $arguments)
    {
        if (!$this->apiClient) {
            return false;
        }

        $loggedArgs = $arguments ? json_encode($arguments, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "";
        $this->logger->writeRow(sprintf("Method %s to API; args: %s", $method, $loggedArgs));

        try {
            $response = call_user_func_array([$this->apiClient, $method], $arguments);
            $this->logger->writeRow(sprintf("From API: %s", is_array($response) ? json_encode($response) : json_encode($response->getResponse())));

            return $response;
        } catch (FailedResponseException $e) {
            $response = $e->getResponse();
            if ($response->message) {
                $errors = isset($response->errors) ?
                    json_encode($response->errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "";

                $this->logger->writeRow(sprintf("[%s] %s; errors: %s; args: %s", $method, $response->message, $errors, $loggedArgs));
            }
        } catch (CurlException|InvalidJsonException $e) {
            $this->logger->writeRow(sprintf("[%s] %s; args: %s", $method, $e->getMessage(), $loggedArgs));
        }

        return false;
    }

    /**
     * Initialize KeyCrm API client.
     */
    public function init(): void
    {
        if ($this->apiKey !== null) {
            $this->apiClient = new ApiClientFactory($this->apiKey);
        } else {
            // Обработка ситуации, когда apiKey равен null
            $this->apiClient = null;
        }
    }

    /**
     * Set API URL.
     *
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }

    /**
     * Set API key.
     *
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Check if the proxy is configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->apiKey !== null;
    }

    /**
     * Get error text message.
     *
     * @param string $property
     *
     * @return string
     */
    public function getErrorText(string $property): string
    {
        return $this->{$property};
    }
}
