<?php

namespace Cubeta\CubetaStarter\Logs;

class CubeError
{
    public string $message;
    public ?string $affectedFilePath = null;
    public ?string $happenedWhen = null;

    /**
     * @param string $message
     * @param string|null $affectedFilePath
     * @param string|null $happenedWhen
     */
    public function __construct(string $message, ?string $affectedFilePath = null, ?string $happenedWhen = null)
    {
        $this->message = $message;
        $this->affectedFilePath = $affectedFilePath;
        $this->happenedWhen = $happenedWhen;
    }

    public function getMessage(): string
    {
        $msg = "Error : " . $this->message . "\n";
        if ($this->affectedFilePath) $msg .= "Affected : {$this->affectedFilePath} \n";
        if ($this->happenedWhen) $msg .= "Happened When : {$this->happenedWhen} \n";

        return $msg;
    }

    public function getHtml(): string
    {
        $msg = "<div class='p-3 d-flex gap-1 flex-column justify-content-between p-2 border border-danger rounded-3 border-2 w-100' style='position: relative'>
                <span style='position: absolute; top: -25%; left: 1%' class='bg-danger rounded-2 p-1 fw-bold'>Error</span>
                    <div class='w-100'>{$this->message}</div>
                ";
        $msg .= $this->affectedFilePath ? "<div class='w-100'><span class='bg-danger-light'>Affected</span> : {$this->affectedFilePath}</div>" : "";
        $msg .= $this->happenedWhen ? "<div class='w-100'><span class='bg-danger-light'>Happened When</span> : {$this->happenedWhen}</div>" : "";

        $msg .= "</div>";
        return $msg;
    }

    public function error(): string
    {
        return $this->message;
    }
}
