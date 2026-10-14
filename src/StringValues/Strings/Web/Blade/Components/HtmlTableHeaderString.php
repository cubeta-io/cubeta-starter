<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components;

class HtmlTableHeaderString
{
    private string $header;

    private ?string $translationKey = null;

    public function __construct(string $header)
    {
        $this->header = $header;
    }

    public function translationKey(string $translationKey): self
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    public function __toString(): string
    {
        return $this->translationKey
            ? "<th>{{ trans('site.{$this->translationKey}') }}</th>"
            : "<th>$this->header</th>";
    }
}
