<?php

namespace Cubeta\CubetaStarter\Postman\PostmanRequest;

use Cubeta\CubetaStarter\Postman\PostmanEvent;
use Cubeta\CubetaStarter\Postman\PostmanObject;
use Illuminate\Support\Collection;

/**
 * @implements PostmanObject<PostmanRequest>
 */
class PostmanRequest implements PostmanObject
{
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    public string $name;

    public string $method = self::GET;

    /** @var array<PostmanEvent> */
    public array $events;

    /** @var array<RequestHeader> */
    public array $header = [];

    public RequestBody|null $body = null;

    public RequestUrl $url;

    public ?RequestAuth $auth = null;

    public array|null $response = null;

    /**
     * @param string $name
     * @param string $method
     * @param PostmanEvent[] $events
     * @param RequestHeader[] $header
     * @param RequestBody|null $body
     * @param RequestUrl $url
     * @param RequestAuth|null $auth
     * @param array $response
     */
    public function __construct(string $name, string $method, RequestUrl $url, array $events = [], array $header = [], ?RequestBody $body = null, ?RequestAuth $auth = null, array $response = [])
    {
        $this->name = $name;
        $this->method = $method;
        $this->events = $events;
        $this->header = $header;
        $this->body = $body;
        $this->url = $url;
        $this->auth = $auth;
        $this->response = $response;
    }


    /**
     * @param array{
     *     name:string ,
     *      request:array{
     *          method:string ,
     *          header:array{key:string , value:string , type:string} ,
     *          body:array{mode:string|null , formdata:array{key:string|null , value:string|null , type:string|null}} ,
     *          url:array ,
     *          auth:array{type:string|null , bearer:string|null}
     *     } ,
     *     response:array,
     *     event:array{script:array{exec:string , type:string|null} , listen:string}
     *     } $data
     * @return self
     */
    public static function serialize(array $data): PostmanRequest
    {
        return new self(
            $data['name'],
            $data['request']['method'] ?? self::GET,
            RequestUrl::serialize($data['request']['url']),
            isset($data['event']) ? array_map(fn($event) => PostmanEvent::serialize($event), $data['event']) : [],
            isset($data['request']['header']) ? array_map(fn($header) => RequestHeader::serialize($header), $data['request']['header']) : [],
            isset($data['request']['body']) ? RequestBody::serialize($data['request']['body']) : null,
            isset($data['request']['auth']) ? RequestAuth::serialize($data['request']['auth']) : null,
            $data['response'] ?? []
        );
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{
     *      name:string ,
     *      request:array{
     *           method:string ,
     *           header:array{key:string , value:string , type:string} ,
     *           body:array{mode:string|null , formdata:array{key:string|null , value:string|null , type:string|null}} ,
     *           url:array ,
     *           auth:array{type:string|null , bearer:string|null}
     *      } ,
     *      response:array,
     *      event:array{script:array{exec:string , type:string|null} , listen:string}
     *      }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'event' => collect($this->events)->filter(fn($event) => $event->toArray())->toArray(),
            'request' => [
                'auth' => $this->auth?->toArray(),
                'method' => $this->method,
                'header' => collect($this->header)->filter(fn($header) => $header->toArray())->toArray(),
                'url' => $this->url->toArray(),
                'body' => $this->body?->toArray(),
            ],
            'response' => $this->response
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
