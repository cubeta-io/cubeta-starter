<?php

namespace Cubeta\CubetaStarter\Logs\Warnings;

use Cubeta\CubetaStarter\Logs\CubeWarning;

class ContentAlreadyExist extends CubeWarning
{
    private string $content;
    private string $filePath;

    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        $this->content = $content;
        $this->filePath = $filePath;
        parent::__construct(
            "The Content :\n```\n{$content}\n```\nAlready Exists In : [$filePath]",
            $context
        );
    }

    public function getHtml(): string
    {
        $msg = "<div style='position: relative' class='my-5 p-3 w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-warning rounded-3 border-2'>
                    <span style='position: absolute; top: -15%; left: 1%' class='bg-warning rounded-2 p-1 fw-bold text-black'>Warning</span>
                    <div class='w-100'>
                        The Content : <br>
                        <code> {$this->content}</code><br>
                        Already Exists In : [{$this->filePath}]
                    </div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-warning-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
