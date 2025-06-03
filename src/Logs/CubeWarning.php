<?php

namespace Cubeta\CubetaStarter\Logs;

class CubeWarning
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
        $msg = "Warning : {$this->message}";

        if ($this->context) $msg .= "\n\rContext : {$this->context}";

        return $msg;
    }

    public function warning(): string
    {
        return $this->message;
    }

    public function getHtml(): string
    {
        $msg = "<div class='p-3 w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-warning rounded-3 border-2' style='position: relative'>
        <span  style='position: absolute; top: -25%; left: 1%' class='bg-warning rounded-2 p-1 fw-bold text-black'>Warning</span>
                    <div class='w-100'>{$this->message}</div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-warning-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
