<?php

namespace App;

use App\Client\DefaultClient;

class ExampleProvider
{
    const BASE_URL = 'http://example.com/';

    /**
     * @var DefaultClient
     */
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new DefaultClient('Example', self::BASE_URL);
    }

    public function addExample(array $example)
    {
        return $this->httpClient->post('comment', $example)->getResponse();
    }

    public function updateExample($id, array $exampleUpdate)
    {
        return $this->httpClient->put('comment/' . $id, $exampleUpdate)->getResponse();
    }

    public function getExamples()
    {
        return $this->httpClient->get('comments')->getResponse();
    }
}
