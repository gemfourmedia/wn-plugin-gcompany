<?php namespace GemFourMedia\GCompany\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GemFourMedia\GCompany\Models\Address;
use Event;

class GCompanyAddress extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;

    public $cssClass;
    public $title;
    public $subtitle;
    public $mode;
    public $address;
    public $addresses;
    public $detailPage;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.address.name',
            'description' => 'gemfourmedia.gcompany::lang.components.address.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'mode' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.mode',
                'type' => 'dropdown',
                'default' => 'all',
                'options' => [
                    'all' => 'gemfourmedia.gcompany::lang.components.address.props.mode_all',
                    'default' => 'gemfourmedia.gcompany::lang.components.address.props.mode_default',
                    'single' => 'gemfourmedia.gcompany::lang.components.address.props.mode_single',
                ],
                'showExternalParam' => false,
            ],
            'filterType' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.filterType',
                'group' => 'gemfourmedia.gcompany::lang.components.address.props.group_mode_all',
                'type' => 'dropdown',
                'default' => '',
                'showExternalParam' => false,
            ],
            'exceptDefault' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.exceptDefault',
                'group' => 'gemfourmedia.gcompany::lang.components.address.props.group_mode_all',
                'type' => 'checkbox',
                'default' => true,
                'showExternalParam' => false,
            ],
            'detailPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.detailPage',
                'group' => 'gemfourmedia.gcompany::lang.components.address.props.group_mode_all',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'addressCode' => [
                'title' => 'gemfourmedia.gcompany::lang.components.address.props.address_code',
                'comment' => 'gemfourmedia.gcompany::lang.components.address.props.address_code_comment',
                'group' => 'gemfourmedia.gcompany::lang.components.address.props.group_mode_single',
                'type' => 'dropdown',
                'default' => '{{:code}}',
            ],
        ];
    }

    public function getDetailPageOptions()
    {
        return ['' => 'Select Page'] + $this->listGCompanyCmsPages('companyAddress');
    }

    public function getAddressCodeOptions()
    {
        return ['' => 'Select Address'] + Address::get()->lists('name', 'code');
    }

    public function getFilterTypeOptions()
    {
        return ['' => 'Select Type'] + (new Address)->getTypeOptions();
    }

    public function prepareVars()
    {
        // Widget vars
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');
        
        // Display mode
        $this->mode = $this->page['mode'] = $this->property('mode');
        
        // Mode display multiple
        $this->detailPage = $this->page['detailPage'] = $this->property('detailPage');
        $this->addresses = $this->page['addresses'] = $this->loadAddresses();
        
        // Mode display single
        $this->address = $this->page['address'] = $this->loadAddress();
    }

    public function init() {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            foreach ($params as $paramName => $paramValue) {
                $records = Address::transWhere($paramName, $paramValue, $oldLocale)->first();
                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams[$paramName] = $records[$paramName];
                }
            }
            return $newParams;
        });
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    public function onRender()
    {
        if (!$this->address || $this->addresses) {
            $this->prepareVars();
        }
    }

    // Load multiple
    public function loadAddresses()
    {
        if (!($this->property('mode') === 'all')) return;
        $model = new Address;
        if ($this->property('exceptDefault')) {
            $model = $model->isNotDefault();
        }
        if ($this->property('filterType')) {
            $model = $model->filterByType($this->property('filterType'));
        }
        $items = $model->get();
        
        if (!$items) return;
        
        if ($this->detailPage) {
            $items->each(function ($item) {
                $item->url = $this->controller->pageUrl($this->detailPage, ['code' => $item->code, 'id' => $item->id]);
            });
        }
        return $items;
    }

    // Load single address; mode: Single/Default
    public function loadAddress(){
        if ($this->property('mode') === 'all') return;
        $method = 'load'.ucfirst($this->property('mode')).'Address';
        
        if (!$address = $this->{$method}()) return null;

        $this->page->title = $address->meta_title ?? $address->name;
        $this->page->meta_description = $address->meta_description ? $address->meta_description : \Html::strip($address->desc);
        $this->page->meta_keywords = $address->meta_keywords;
        
        return $address;

    }

    // Load Single address
    public function loadSingleAddress()
    {
        if (!$code = $this->property('addressCode')) return;

        $model = new Address;
        $address = $model->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')
            ? $model->transWhere('code', $code)
            : $model->where('code', $code);

        try {
            $address = $address->with('images')->first();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        return ($address)?:null;
    }

    // Load Default address
    public function loadDefaultAddress()
    {
        return Address::with('images')->isDefault()->first();
    }

}
