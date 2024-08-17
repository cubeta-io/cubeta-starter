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
        $msg = "<div style='position: relative' class='my-5 p-3 w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-success rounded-3 border-2'>
                    <span style='position: absolute; top: -15%; left: 1%' class='bg-success rounded-2 p-1 fw-bold'>Info</span>
                    <div class='w-100'>
                        The Content : <br>
                        <code> {$this->content}</code><br>
                        Has Been Appended Successfully To : [{$this->filePath}]
                    </div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-success-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
