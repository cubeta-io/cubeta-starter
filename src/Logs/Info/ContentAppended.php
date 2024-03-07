<?php

namespace Cubeta\CubetaStarter\Logs\Info;

use Cubeta\CubetaStarter\Logs\CubeInfo;

class ContentAppended extends CubeInfo
{
    private string $content;
    private string $filePath;

    public function __construct(string $content, string $filePath)
    {
        $this->content = $content;
        $this->filePath = $filePath;

        parent::__construct(
            "The Content :\n```\n{$content}\n```\nHas Been Appended Successfully To : [{$filePath}] \n"
        );
    }

    public function getHtml(): string
    {
        $msg = "<div class='w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-success rounded-3 border-2'>
                    <div class='w-100'>
                        <span class='bg-success rounded-2 p-1 fw-bold'>Info</span> :  The Content : <br>
                        <code> {$this->content}</code><br>
                        Has Been Appended Successfully To : [{$this->filePath}]
                    </div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-success-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
