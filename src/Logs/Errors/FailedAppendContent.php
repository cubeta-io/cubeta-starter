<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeError;

class FailedAppendContent extends CubeError
{
    private string $content;
    private string $filePath;

    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        $this->content = FileUtils::formatCodeString($content);
        $this->filePath = $filePath;

        parent::__construct(
            message: "Failed To Append The Following Content :" .
            "\n\r```\n\r{$content}\n\r```" .
            "\n\rTo : [$filePath]",
            happenedWhen: $context
        );
    }

    public function getHtml(): string
    {
        $msg = "<div style='position:relative;' class='p-3 w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-danger rounded-3 border-2'>
                    <span style='position: absolute; top: -5%; left: 1%' class='bg-danger rounded-2 p-1 fw-bold'>Error</span> : Failed To Append The Following Content :<br>
                    <div class='w-100'>
                        <br>
                        <pre>
                            <code>
                                {$this->content}
                            </code>
                        </pre>
                        <br>
                        To : [{$this->filePath}]
                     </div>
                ";
        $msg .= $this->happenedWhen ? "<div class='w-100'><span class='bg-danger-light'>Happened When</span> : {$this->happenedWhen}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
