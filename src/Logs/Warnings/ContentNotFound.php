<?php

namespace Cubeta\CubetaStarter\Logs\Warnings;

use Cubeta\CubetaStarter\Logs\CubeWarning;

class ContentNotFound extends CubeWarning
{
    private string $content;
    private string $filePath;

    /**
     * @param string $content
     * @param string $filePath
     * @param string|null $context
     */
    public function __construct(string $content, string $filePath, ?string $context = null)
    {
        $this->content = $content;
        $this->filePath = $filePath;

        parent::__construct("Content :\n```\n{$content}\n```\nCouldn't Be Found In [$filePath]", $context);
    }

    public function getHtml(): string
    {
        $msg = "<div class='w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-warning rounded-3 border-2'>
                    <div class='w-100'>
                        <span class='bg-warning rounded-2 p-1 fw-bold'>Warning</span> :  The Content : <br>
                        <code> {$this->content}</code><br>
                        Couldn't Be Found In : [{$this->filePath}]
                    </div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-warning-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
