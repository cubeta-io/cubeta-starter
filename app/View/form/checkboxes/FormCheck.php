<?php

namespace Cubeta\CubetaStarter\App\View\Form\Checkboxes;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormCheck extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.form.checkboxes.form-check.blade.php');
    }
}
