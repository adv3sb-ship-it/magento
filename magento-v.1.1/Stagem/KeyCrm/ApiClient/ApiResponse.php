<?php

namespace Stagem\KeyCrm\ApiClient;

use BadMethodCallException;
use InvalidArgumentException;
use Stagem\KeyCrm\ApiClient\Exceptions\InvalidJsonException;

class ApiResponse implements \ArrayAccess
{
    protected int $statusCode;
    protected array $response;

    /**
     * ApiResponse constructor.
     *
     * @param int        $statusCode
     * @param mixed|null $responseBody
     *
     * @throws InvalidJsonException
     */
    public function __construct(int $statusCode, mixed $responseBody = null)
    {
        $this->statusCode = $statusCode;

        if (!empty($responseBody)) {
            $response = json_decode($responseBody, true);

            if (!$response && JSON_ERROR_NONE !== ($error = json_last_error())) {
                throw new InvalidJsonException(
                    "Invalid JSON in the API response body. Error code #$error",
                    $error
                );
            }

            $this->response = $response;
        }
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode < 400;
    }

    public function __call(string $name, array $arguments)
    {
        $propertyName = strtolower(substr($name, 3, 1)) . substr($name, 4);

        if (!isset($this->response[$propertyName])) {
            throw new InvalidArgumentException("Method \"$name\" not found");
        }

        return $this->response[$propertyName];
    }

    public function __get(string $name)
    {
        if (!isset($this->response[$name])) {
            throw new InvalidArgumentException("Property \"$name\" not found");
        }

        return $this->response[$name];
    }

    public function __isset(string $name): bool
    {
        return isset($this->response[$name]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('This activity not allowed');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('This call not allowed');
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->response[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!isset($this->response[$offset])) {
            throw new InvalidArgumentException("Property \"$offset\" not found");
        }

        return $this->response[$offset];
    }
}