<?php

namespace Cubeta\CubetaStarter\Logs\Info;

use Cubeta\CubetaStarter\Logs\CubeInfo;

class SuccessGenerating extends CubeInfo
{
    private $fileName;
    private $filePath;
    public function __construct(string $fileName, string $filePath, ?string $context = null)
    {
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        parent::__construct(
            "($fileName) Generated Successfully \nPath : [$filePath]\n"
            , $context
        );
    }

    public function getHtml(): string
    {
        $msg = "<div style='position: relative' class='p-3 w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-success rounded-3 border-2'>
                    <span style='position: absolute; top: -25%; left: 1%' class='bg-success rounded-2 p-1 fw-bold'>Info</span>
                    <div class='w-100'>
                        File : ({$this->fileName}) Generated Successfully
                        Path : [{$this->filePath}]
                    </div>
                ";
        $msg .= $this->context ? "<div class='w-100'><span class='bg-success-light'>Context</span> : {$this->context}</div>" : "";
        $msg .= "</div>";
        return $msg;
    }
}
