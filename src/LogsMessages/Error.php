<?php

namespace Cubeta\CubetaStarter\LogsMessages;

abstract class Error
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
        return $this->message .
            "\n" .
            "Affected : {$this->affectedFilePath} \n" .
            "Happened When : {$this->happenedWhen} \n";
    }
}
