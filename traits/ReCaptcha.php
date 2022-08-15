<?php

namespace GemFourMedia\GCompany\Traits;

use Session;
use GemFourMedia\GCompany\Models\Setting;
use Winter\Translate\Classes\Translator;

trait ReCaptcha {

    /**
     * @var RainLab\Translate\Classes\Translator Translator object.
     */
    protected $translator;

    /**
     * @var string The active locale code.
     */
    public $activeLocale;

    public function init() {
        if ($this->hasTranslatePlugin()) {
            $this->translator = Translator::instance();
        }
    }

    private function isReCaptchaEnabled() {
        return ($this->property('recaptcha_enabled') && Setting::get('recaptcha_site_key') != '' && Setting::get('recaptcha_secret_key') != '');
    }

    private function isReCaptchaMisconfigured() {
        return ($this->property('recaptcha_enabled') && (Setting::get('recaptcha_site_key') == '' || Setting::get('recaptcha_secret_key') == ''));
    }

    private function getReCaptchaLang($lang='') {
        if ($this->hasTranslatePlugin()) {
            $lang = '&hl=' . $this->activeLocale = $this->translator->getLocale();
        } else {
            $lang = '&hl=' . $this->activeLocale = app()->getLocale();
        }
        return $lang;
    }

    private function loadReCaptcha() {
        $this->addJs('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit'.$this->getReCaptchaLang(), ['async', 'defer']);
        $this->addJs('assets/js/recaptcha.js');
    }

    public function hasTranslatePlugin() :bool {
        return class_exists('\Winter\Translate\Classes\Translator') && class_exists('\Winter\Translate\Models\Message');
    }
}
