<?php

namespace App\View\Components\Datatable\common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterSelectDropDown extends Component
{
    /**
     * Create a new component instance.
     */
    public $id;
    public $name;
    public $options;
    public $isCustom;
    public $isCustomCol;
    public $haslabel;
    public function __construct($id, $name, $options, $isCustom = null, $isCustomCol = null, $haslabel = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->options = $options;
        $this->isCustom = $isCustom;
        $this->isCustomCol = $isCustomCol;
        $this->haslabel = $haslabel;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.datatable.common.filter-select-drop-down');
    }
}
