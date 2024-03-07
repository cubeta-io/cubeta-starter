<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Logs\CubeError;

class FailedAppendContent extends CubeError
{
    private string $content;
    private string $filePath;

    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        $this->content = $content;
        $this->filePath = $filePath;

        parent::__construct(
            message: "Failed To Append The Following Content : \n" .
            "```\n{$content}\n```\n" .
            "To : [$filePath]",
            happenedWhen: $context
        );
    }

    public function getHtml(): string
    {
        $msg = "<div class='w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-danger rounded-3 border-2'>
                    <div class='w-100'>
                        <span class='bg-danger rounded-2 p-1 fw-bold'>Error</span> :  Failed To Append The Following Content :<br>
                        <code> {$this->content}</code><br>
                        To : [{$this->filePath}]
                     </div>
                ";
        $msg .= $this->happenedWhen ? "<div class='w-100'><span class='bg-danger-light'>Happened When</span> : {$this->happenedWhen}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
