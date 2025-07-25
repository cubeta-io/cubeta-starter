<?php

namespace Cubeta\CubetaStarter\Logs;

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
            return "Info : {$this->message} \n\rContext : {$this->context}";
        }
        return "Info : {$this->message}";
    }

    public function info(): string
    {
        return $this->message;
    }

    public function getHtml(): string
    {
        $msg = "<div class='p-3 w-100 d-flex gap-1 p-2 flex-column justify-content-between border border-success rounded-3 border-2' style='position: relative'>
                <span style='position: absolute; top: -25%; left: 1%' class='bg-success rounded-2 p-1 fw-bold'>Info</span>
                    <div class='w-100'>{$this->message}</div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-success-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
