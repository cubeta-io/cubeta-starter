<?php

namespace Cubeta\CubetaStarter\LogsMessages;

class CubeInfo
{
    protected string $message;
    protected ?string $context;

    /**
     * @param string $message
     * @param string|null $context
     */
    public function __construct(string $message, ?string $context = null)
    {
        $this->message = $message;
        $this->context = $context;
    }

    public function getMessage(): string
    {
        if ($this->context) {
            return "Info : {$this->message} \n
                Context : {$this->context} \n";
        }
        return "Info : {$this->message} \n";
    }
}
