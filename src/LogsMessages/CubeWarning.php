<?php

namespace Cubeta\CubetaStarter\LogsMessages;

abstract class CubeWarning
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
        $msg = "Warning : {$this->message} \n";

        if ($this->context) $msg .= "Context : {$this->context} \n";

        return $msg;
    }
}
