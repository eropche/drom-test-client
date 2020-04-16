<?php

namespace App\Client;

use App\Client\ClientException;

/**
 * Базовый клиент веб серсвисов
 */
class DefaultClient
{
    const METHOD_GET    = 'GET';

    const METHOD_POST   = 'POST';

    const METHOD_PUT    = 'PUT';

    const METHOD_DELETE = 'DELETE';

    /**
     * @var string Имя веб сервиса
     */
    protected $serviceName;

    /**
     * @var string Host сервиса
     */
    protected $serviceHost;

    /**
     * @var string Идентификатор приложения
     */
    protected $appUid;

    /**
     * Порт сервиса
     *
     * @var int
     */
    protected $port;

    /**
     * Путь по который обращаемся к конкретному интерфейсу
     * например (/v1.0/api-path)
     *
     * @var string
     */
    protected $apiPath;

    /**
     * Результат запроса
     *
     * @var array
     */
    protected $response;

    /**
     * Уникальный идентификатор запроса
     *
     * @var string
     */
    protected $requestId;

    /**
     * Опции запроса
     *
     * @var array
     */
    protected $curlOptions;

    /**
     * @param string $serviceName Наименование сервиса
     * @param string $serviceHost Адрес сервиса
     * @param string $appUid      Идентификатор приложения с которго отправляется запрос
     */
    public function __construct(string $serviceName, string $serviceHost, string $appUid = null)
    {
        $this->serviceName  = $serviceName;
        $this->serviceHost  = $serviceHost;
        $this->appUid       = $appUid;
    }

    /**
     * @inheritdoc
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * Возвращает уникальный Id для запроса
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Устанавливает уникальный Id для запроса
     *
     * @return string
     */
    protected function setRequestId($id): void
    {
        $this->requestId = $id;
    }

    /**
     * Возвращает dsn для сервиса
     *
     * @return string
     */
    public function getDsn(): string
    {
        return 'tcp://' . $this->serviceHost . ':' . $this->getPort();
    }

    /**
     * Возвращает идентификатор приложения
     *
     * @return string
     */
    public function getAppUid(): ?string
    {
        return $this->appUid;
    }

    /**
     * Отправить POST запрос
     *
     * @param string $apiUrl
     * @param array  $arguments
     *
     * @return DefaultClient
     */
    public function post($apiUrl = '/', $arguments = [])
    {
        return $this->execute($apiUrl, $arguments, self::METHOD_POST);
    }

    /**
     * Отправить GET запрос
     *
     * @param string $apiUrl
     * @param array  $arguments
     *
     * @return DefaultClient
     */
    public function get($apiUrl = '/', $arguments = [])
    {
        return $this->execute($apiUrl, $arguments, self::METHOD_GET);
    }

    /**
     * Отправить PUT запрос
     *
     * @param string $apiUrl
     * @param array  $arguments
     *
     * @return DefaultClient
     */
    public function put($apiUrl = '/', $arguments = [])
    {
        return $this->execute($apiUrl, $arguments, self::METHOD_PUT);
    }

    /**
     * @inheritDoc
     */
    public function setRequest(string $request): void
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    protected function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Выполнить запрос
     *
     * @param string $apiUrl
     * @param array $arguments
     * @param int $method
     */
    public function execute(string $apiUrl = '/', array $arguments = [], string $method = self::METHOD_GET)
    {
        $this->prepare($apiUrl, $arguments, $method);
        $curl = curl_init();
        foreach ($this->curlOptions as $option => $value) {
            curl_setopt($curl, $option, $value);
        }
        $response = curl_exec($curl);

        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        curl_close($curl);

        if ($errorNumber > 0) {
            throw ClientException::curlError($errorMessage);
        }
        $this->response = $response;

        return $this;
    }

    /**
     * Получить ответ с сервиса
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Получить порт.
     * Если порт не задан явно возвращается
     * порт по умолчанию - 80
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port ? $this->port : 80;
    }

    /**
     * Получить хост
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->serviceHost;
    }

    /**
     * Установить путь API
     *
     * @param string $path
     *
     * @throws ClientException
     */
    public function setApiPath($path)
    {
        if (!$path) {
            throw ClientException::apiPathCannotBeNull();
        }

        $this->apiPath = '/' . ltrim($path, '/');
    }

    /**
     * Возвращает путь у API
     *
     * @return string
     *
     * @throws ClientException
     */
    public function getApiPath(): string
    {
        if (!$this->apiPath) {
            throw ClientException::apiPathNotFound();
        }

        return $this->apiPath;
    }

    /**
     * Сформировать запрос
     *
     * @param array $data
     * @param string $method
     *
     * @return string
     */
    protected function buildRequest($data, $method = self::METHOD_GET): string
    {
        $headers =
            $method . ' ' . $this->getApiPath() . ' HTTP/1.0'   . PHP_EOL
            . 'Host: '. $this->getHost()                        . PHP_EOL
            . 'Content-Type: application/json'                  . PHP_EOL
            . 'Cache-Control: no-cache'                         . PHP_EOL
            . 'Content-Length: '. strlen($data)         . PHP_EOL
        ;

        if (null !== $appId = $this->getAppUid()) {
            $headers .= "App-Uid: " . $appId . PHP_EOL;
        }

        return $headers . PHP_EOL . $data;
    }

    /**
     * Очистить результат ответа сервиса
     *
     * @return void
     */
    protected function clearResponse(): void
    {
        $this->response = null;
    }

    /**
     * Подготовить запрос
     *
     * @param string $apiUrl
     * @param array $arguments
     * @param string $method
     *
     * @return void
     */
    protected function prepare(string $apiUrl = '/', array $arguments = [], string $method = self::METHOD_GET): void
    {
        $this->clearResponse();
        $this->setApiPath($apiUrl);
        $this->setRequestId(uniqid());
        $this->setBody(json_encode($arguments));
        $this->setRequest($this->buildRequest($this->getBody(), $method));
        $this->setMethod($method);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];
        switch ($method) {
            case self::METHOD_POST:
                $options[CURLOPT_POST] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $method;
        }
        $options[CURLOPT_URL]        = $this->getHost().$apiUrl;
        $options[CURLOPT_POSTFIELDS] = http_build_query($arguments);
        $this->setCurlOptions($options);
    }

    /**
     * Установить опции
     *
     * @param $curlOptions
     */
    protected function setCurlOptions($curlOptions)
    {
        $this->curlOptions = $curlOptions;
    }

    /**
     * Получить опции
     *
     * @return array
     */
    protected function getCurlOptions()
    {
        return $this->curlOptions;
    }
}
