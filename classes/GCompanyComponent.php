<?php namespace GemFourMedia\GCompany\Classes;

use Cms\Classes\ComponentBase;

abstract class GCompanyComponent extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;

    public $cssClass;
    public $title;
    public $subtitle;
    public $enableAssets;

    public function prepareWidget()
    {
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');
        $this->enableAssets = $this->page['enableAssets'] = $this->property('enableAssets');
    }

}
