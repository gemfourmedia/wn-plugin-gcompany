<?php namespace GemFourMedia\GCompany\Components;

use GemFourMedia\GCompany\Classes\GCompanyComponent;
use GemFourMedia\GCompany\Models\Setting;

use Lang;
use Config;
use Request;
use Session;
use Redirect;
use Validator;
use AjaxException;

use Martin\Forms\Models\Record;
use Martin\Forms\Classes\SendMail;
use Illuminate\Support\Facades\Event;
use Winter\Storm\Exception\ValidationException;

class GCompanyForm extends GCompanyComponent
{
    use \GemFourMedia\GCompany\Traits\ReCaptcha;

    public $allowedFields;
    public $rules;
    public $action;

    protected $translator;
    public $activeLocale;

    public $sendNotification;
    public $autoResponse;
    public $enableReCaptcha;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.companyForm.name',
            'description' => 'gemfourmedia.gcompany::lang.components.companyForm.desc',
        ];
    }

    public function defineProperties()
    {
        return [
            'group' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.group.description',
                'type'              => 'string',
                'showExternalParam' => false,
            ],
            'rules' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.rules.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.rules.description',
                'type'              => 'dictionary',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_validation',
                'showExternalParam' => false,
            ],
            'rules_messages' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.rules_messages.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.rules_messages.description',
                'type'              => 'dictionary',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_validation',
                'showExternalParam' => false,
            ],
            'custom_attributes' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.custom_attributes.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.custom_attributes.description',
                'type'              => 'dictionary',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_validation',
                'showExternalParam' => false,
            ],
            'messages_success' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_success.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_success.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_messages',
                'default'           => Lang::get('gemfourmedia.gcompany::lang.components.companyForm.props.messages_success.default'),
                'showExternalParam' => false,
                'validation'        => ['required' => ['message' => Lang::get('gemfourmedia.gcompany::lang.components.companyForm.props.validation_req')]]
            ],
            'messages_errors' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_errors.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_errors.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_messages',
                'default'           => Lang::get('gemfourmedia.gcompany::lang.components.companyForm.props.messages_errors.default'),
                'showExternalParam' => false,
                'validation'        => ['required' => ['message' => Lang::get('gemfourmedia.gcompany::lang.components.companyForm.props.validation_req')]]
            ],
            'messages_partial' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_partial.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.messages_partial.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_messages',
                'showExternalParam' => false
            ],
            'mail_enabled' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_enabled.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_enabled.description',
                'type'              => 'checkbox',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_subject' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_subject.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_subject.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_recipients' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_recipients.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_recipients.description',
                'type'              => 'stringList',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_bcc' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_bcc.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_bcc.description',
                'type'              => 'stringList',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_replyto' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_replyto.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_replyto.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_template' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_template.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_template.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail',
                'showExternalParam' => false
            ],
            'mail_resp_enabled' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_enabled.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_enabled.description',
                'type'              => 'checkbox',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail_resp',
                'showExternalParam' => false
            ],
            'mail_resp_field' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_field.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_field.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail_resp',
                'showExternalParam' => false
            ],
            'mail_resp_from' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_from.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_from.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail_resp',
                'showExternalParam' => false
            ],
            'mail_resp_subject' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_subject.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_resp_subject.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail_resp',
                'showExternalParam' => false
            ],
            'mail_resp_template' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_template.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.mail_template.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_mail_resp',
                'showExternalParam' => false
            ],
            'reset_form' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.reset_form.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.reset_form.description',
                'type'              => 'checkbox',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_settings',
                'showExternalParam' => false
            ],
            'redirect' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.redirect.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.redirect.description',
                'type'              => 'string',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_settings',
                'showExternalParam' => false
            ],
            'inline_errors' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.inline_errors.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.inline_errors.description',
                'type'              => 'dropdown',
                'options'           => ['disabled' => 'gemfourmedia.gcompany::lang.components.companyForm.props.inline_errors.disabled', 'display' => 'gemfourmedia.gcompany::lang.components.companyForm.props.inline_errors.display', 'variable' => 'gemfourmedia.gcompany::lang.components.companyForm.props.inline_errors.variable'],
                'default'           => 'disabled',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_settings',
                'showExternalParam' => false
            ],
            'js_on_success' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.js_on_success.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.js_on_success.description',
                'type'              => 'text',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_settings',
                'showExternalParam' => false
            ],
            'js_on_error' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.js_on_error.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.js_on_error.description',
                'type'              => 'text',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_settings',
                'showExternalParam' => false
            ],
            'allowed_fields' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.allowed_fields.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.allowed_fields.description',
                'type'              => 'stringList',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_security',
                'showExternalParam' => false
            ],
            'sanitize_data' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.sanitize_data.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.sanitize_data.description',
                'type'              => 'dropdown',
                'options'           => ['disabled' => 'gemfourmedia.gcompany::lang.components.companyForm.props.sanitize_data.disabled', 'htmlspecialchars' => 'gemfourmedia.gcompany::lang.components.companyForm.props.sanitize_data.htmlspecialchars'],
                'default'           => 'disabled',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_security',
                'showExternalParam' => false
            ],
            'anonymize_ip' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.anonymize_ip.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.anonymize_ip.description',
                'type'              => 'dropdown',
                'options'           => ['disabled' => 'gemfourmedia.gcompany::lang.components.companyForm.props.anonymize_ip.disabled', 'partial' => 'gemfourmedia.gcompany::lang.components.companyForm.props.anonymize_ip.partial', 'full' => 'gemfourmedia.gcompany::lang.components.companyForm.props.anonymize_ip.full'],
                'default'           => 'disabled',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_security',
                'showExternalParam' => false
            ],
            'recaptcha_enabled' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_enabled.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_enabled.description',
                'type'              => 'checkbox',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_recaptcha',
                'showExternalParam' => false
            ],
            'recaptcha_theme' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_theme.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_theme.description',
                'type'              => 'dropdown',
                'options'           => ['light' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_theme.light', 'dark' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_theme.dark'],
                'default'           => 'light',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_recaptcha',
                'showExternalParam' => false
            ],
            'recaptcha_type' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_type.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_type.description',
                'type'              => 'dropdown',
                'options'           => ['image' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_type.image', 'audio' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_type.audio'],
                'default'           => 'image',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_recaptcha',
                'showExternalParam' => false
            ],
            'recaptcha_size' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_size.title',
                'description'       => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_size.description',
                'type'              => 'dropdown',
                'options'           => [
                    'normal' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_size.normal',
                    'compact' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_size.compact',
                    'invisible' => 'gemfourmedia.gcompany::lang.components.companyForm.props.recaptcha_size.invisible',
                ],
                'default'           => 'normal',
                'group'             => 'gemfourmedia.gcompany::lang.components.companyForm.props.group_recaptcha',
                'showExternalParam' => false
            ],
        ];
    }

    public function prepareVars()
    {
        // Form render setting
        
        $this->enableReCaptcha = $this->page['enableReCaptcha'] = $this->property('enableReCaptcha');
        
        // Form action setting
        $this->sendNotification = $this->page['sendNotification'] = $this->property('sendNotification');
        $this->autoResponse = $this->page['autoResponse'] = $this->property('autoResponse');
        
    }

    public function onSubmit()
    {
        // FLASH PARTIAL
        $flash_partial = $this->property('messages_partial', '@flash.htm');

        // CSRF CHECK
        if (Config::get('cms.enableCsrfProtection') && (Session::token() != post('_token'))) {
            throw new AjaxException(['#' . $this->alias . '_forms_flash' => $this->renderPartial($flash_partial, [
                'status'  => 'error',
                'type'    => 'danger',
                'content' => Lang::get('martin.forms::lang.components.shared.csrf_error'),
            ])]);
        }

        // LOAD TRANSLATOR PLUGIN
        if ($this->hasTranslatePlugin()) {
            $translator = \Winter\Translate\Classes\Translator::instance();
            $translator->loadLocaleFromSession();
            $locale = $translator->getLocale();
            \Winter\Translate\Models\Message::setContext($locale);
        }

        // FILTER ALLOWED FIELDS
        $allow = $this->property('allowed_fields');
        if (is_array($allow) && !empty($allow)) {
            foreach ($allow as $field) {
                $post[$field] = post($field);
            }
            if ($this->isReCaptchaEnabled()) {
                $post['g-recaptcha-response'] = post('g-recaptcha-response');
            }
        } else {
            $post = post();
        }

        // SANITIZE FORM DATA
        if ($this->property('sanitize_data') == 'htmlspecialchars') {
            $post = $this->array_map_recursive(function ($value) {
                return htmlspecialchars($value, ENT_QUOTES);
            }, $post);
        }

        // VALIDATION PARAMETERS
        $rules = (array)$this->property('rules');
        $msgs  = (array)$this->property('rules_messages');
        $custom_attributes = (array)$this->property('custom_attributes');

        // TRANSLATE CUSTOM ERROR MESSAGES
        if ($this->hasTranslatePlugin()) {
            foreach ($msgs as $rule => $msg) {
                $msgs[$rule] = \Winter\Translate\Models\Message::trans($msg);
            }
        }

        // ADD reCAPTCHA VALIDATION
        if ($this->isReCaptchaEnabled() && $this->property('recaptcha_size') != 'invisible') {
            $rules['g-recaptcha-response'] = 'required';
        }

        // DO FORM VALIDATION
        $validator = Validator::make($post, $rules, $msgs, $custom_attributes);

        // NICE reCAPTCHA FIELD NAME
        if ($this->isReCaptchaEnabled()) {
            $fields_names = ['g-recaptcha-response' => 'reCAPTCHA'];
            $validator->setAttributeNames(array_merge($fields_names, $custom_attributes));
        }

        // VALIDATE ALL + CAPTCHA EXISTS
        if ($validator->fails()) {

            // GET DEFAULT ERROR MESSAGE
            $message = $this->property('messages_errors');

            // LOOK FOR TRANSLATION
            if ($this->hasTranslatePlugin()) {
                $message = \Winter\Translate\Models\Message::trans($message);
            }

            // THROW ERRORS
            if ($this->property('inline_errors') == 'display') {
                throw new ValidationException($validator);
            } else {
                throw new AjaxException($this->_exceptionResponse($validator, [
                    'status'  => 'error',
                    'type'    => 'danger',
                    'title'   => $message,
                    'list'    => $validator->messages()->all(),
                    'errors'  => json_encode($validator->messages()->messages()),
                    'jscript' => $this->property('js_on_error'),
                ]));
            }

        }

        // IF FIRST VALIDATION IS OK, VALIDATE CAPTCHA vs GOOGLE
        // (this prevents to resolve captcha after every form error)
        if ($this->isReCaptchaEnabled()) {

            // PREPARE RECAPTCHA VALIDATION
            $rules   = ['g-recaptcha-response'           => 'recaptcha'];
            $err_msg = ['g-recaptcha-response.recaptcha' => Lang::get('martin.forms::lang.validation.recaptcha_error')];

            // DO SECOND VALIDATION
            $validator = Validator::make($post, $rules, $err_msg);

            // VALIDATE ALL + CAPTCHA EXISTS
            if ($validator->fails()) {

                // THROW ERRORS
                if ($this->property('inline_errors') == 'display') {
                    throw new ValidationException($validator);
                } else {
                    throw new AjaxException($this->_exceptionResponse($validator, [
                        'status'  => 'error',
                        'type'    => 'danger',
                        'content' => Lang::get('martin.forms::lang.validation.recaptcha_error'),
                        'errors'  => json_encode($validator->messages()->messages()),
                        'jscript' => $this->property('js_on_error'),
                    ]));
                }

            }

        }

        // REMOVE EXTRA FIELDS FROM STORED DATA
        unset($post['_token'], $post['g-recaptcha-response'], $post['_session_key'], $post['_uploader']);

        // FIRE BEFORE SAVE EVENT
        Event::fire('martin.forms.beforeSaveRecord', [&$post, $this]);

        if (count($custom_attributes)) {
            $post = collect($post)->mapWithKeys(function ($val, $key) use ($custom_attributes) {
                return [array_get($custom_attributes, $key, $key) => $val];
            })->all();
        }

        $record = new Record;
        $record->ip        = $this->getIP();
        $record->created_at = date('Y-m-d H:i:s');

        // SAVE RECORD TO DATABASE
        if (! $this->property('skip_database')) {
            $record->form_data = json_encode($post, JSON_UNESCAPED_UNICODE);
            if ($this->property('group') != '') {
                $record->group = $this->property('group');
            }
            $record->save(null, post('_session_key'));
        }

        // SEND NOTIFICATION EMAIL
        if ($this->property('mail_enabled')) {
            SendMail::sendNotification($this->getProperties(), $post, $record, $record->files);
        }

        // SEND AUTORESPONSE EMAIL
        if ($this->property('mail_resp_enabled')) {
            SendMail::sendAutoResponse($this->getProperties(), $post, $record);
        }

        // FIRE AFTER SAVE EVENT
        Event::fire('martin.forms.afterSaveRecord', [&$post, $this, $record]);

        // CHECK FOR REDIRECT
        if ($this->property('redirect')) {
            return Redirect::to($this->property('redirect'));
        }

        // GET DEFAULT SUCCESS MESSAGE
        $message = $this->property('messages_success');

        // LOOK FOR TRANSLATION
        if ($this->hasTranslatePlugin()) {
            $message = \Winter\Translate\Models\Message::trans($message);
        }

        // DISPLAY SUCCESS MESSAGE
        return ['#' . $this->alias . '_forms_flash' => $this->renderPartial($flash_partial, [
            'status'  => 'success',
            'type'    => 'success',
            'content' => $message,
            'jscript' => $this->prepareJavaScript(),
        ])];
    }

    public function validateReCaptcha($attribute, $value, $parameters) {
        $secret_key = Setting::get('recaptcha_secret_key');
        $recaptcha  = post('g-recaptcha-response');
        $ip         = Request::getClientIp();
        $URL        = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$recaptcha&remoteip=$ip";
        $response   = json_decode(file_get_contents($URL), true);
        return ($response['success'] == true);
    }

}
