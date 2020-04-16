<?php

namespace App\Client;

/**
 * Исключения
 */
class ClientException extends \Exception
{
    /**
     * Сервсис с таким именем не найден
     *
     * @param string $serviceName Наименование сервиса
     * @return \Exception
     */
    public static function serviceNotFound($serviceName): \Exception
    {
        return new self(
            sprintf(
                'Services not found: "%s"',
                $serviceName
            )
        );
    }

    /**
     * Путь к api не может быть пустым или null
     *
     * @return \Exception
     */
    public static function apiPathCannotBeNull(): \Exception
    {
        return new self('Api path cannot be empty or null.');
    }

    /**
     * Если путь к api не задан
     *
     * @return : \Exception
     */
    public static function apiPathNotFound(): \Exception
    {
        return new self('Api path not found.');
    }

    /**
     * Если ошибки в curl
     *
     * @return : \Exception
     */
    public static function curlError($errorMessage): \Exception
    {
        return new self('Curl error: '.$errorMessage);
    }
}
