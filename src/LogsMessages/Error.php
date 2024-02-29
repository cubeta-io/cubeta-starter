<?php

namespace Cubeta\CubetaStarter\LogsMessages;

abstract class Error
{
    protected string $message;
    protected ?string $affectedFilePath = null;
    protected ?string $happenedWhen = null;

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
}
