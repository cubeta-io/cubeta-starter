<?php

namespace Cubeta\CubetaStarter\app\Models;

class Postman
{
    private $instansce;

    public function make()
    {

    }

    private $collection;

    public function __construct($collectionFileName)
    {
        $this->loadCollection($collectionFileName);
    }

    private function loadCollection($collectionFileName)
    {
        $collectionJson = file_get_contents($collectionFileName);
        $this->collection = json_decode($collectionJson, true);
    }

    public function addFolder($folderName)
    {
        $folder = [
            'name' => $folderName,
            'item' => [],
        ];

        $this->collection['item'][] = $folder;
    }

    public function addEndpointToFolder($folderName, $endpointName, $method, $url, $description = '')
    {
        foreach ($this->collection['item'] as &$folder) {
            if ($folder['name'] === $folderName) {
                $endpoint = [
                    'name' => $endpointName,
                    'request' => [
                        'method' => $method,
                        'header' => [],
                        'body' => [],
                        'url' => $url,
                        'description' => $description,
                    ],
                ];

                $folder['item'][] = $endpoint;
            }
        }
    }

    public function initNewCollection($collectionName)
    {
        $this->collection = [
            'info' => [
                'name' => $collectionName,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
        ];
    }

    public function addVariable($variableName, $variableValue)
    {
        $variable = [
            'key' => $variableName,
            'value' => $variableValue,
            'type' => 'text',
        ];

        $this->collection['variable'][] = $variable;
    }

    public function saveCollection($outputFileName)
    {
        file_put_contents($outputFileName, json_encode($this->collection, JSON_PRETTY_PRINT));
    }
}
