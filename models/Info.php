<?php namespace GemFourMedia\GCompany\Models;

use Model;
use GemFourMedia\GCompany\Models\Address;
use Carbon\Carbon;
/**
 * Model
 */
class Info extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    // A unique code
    public $settingsCode = 'gcompany-info';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    protected $appends = ['address'];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'full_name',
        'short_name',
        'business_license',
        'desc',
        'banks',
        'mil',
        'business_hour_short',
        ['slug', 'index' => true]
    ];

    /*
     * Accessors
     * ===
     */
    public function getAddressAttribute()
    {
        return Address::isDefault()->first();
    }

    /*
     * Helper methods
     * ===
     */

    // public function getSocialIconOptions()
    // {
    //     return \GemFourMedia\GWinterHelper\Classes\IconList::getList();
    // }

    public function getTimezoneOptions() {
        $timezoneList = timezone_identifiers_list();
        $timezones = [];
        foreach ($timezoneList as $tz) {
            $timezones[$tz] = $tz;
        }
        return $timezones;
    }

    public function getBusinessHourFullAttribute()
    {
        return $this->prepareBusinessHour($this->business_hour);
    }

    public function getWeekDaysOptions()
    {
        return [
            'monday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.monday')],
            'tuesday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.tuesday')],
            'wednesday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.wednesday')],
            'thursday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.thursday')],
            'friday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.friday')],
            'saturday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.saturday')],
            'sunday' => ['label'=>trans('gemfourmedia.gcompany::frontend.weekdays.sunday')],
        ];
    }

    protected function prepareBusinessHour($business_hour) {

        // Validate data before process
        if (!is_array($business_hour) || empty($business_hour)) return $business_hour;

        // Init useable variables
        $weekdays = $this->getWeekDaysOptions();
        $timezone = \Config::get('app.timezone', 'UTC');
        $days = collect($business_hour);

        // Map busines hours to assign label & transform hours array to open => close
        $businessHours = $days->map(function($data, $day) use ($weekdays, $timezone) {

            $businessHour['label'] = array_get($weekdays, $day.'.label');
            $businessHour['hours'] = trans('gemfourmedia.gcompany::frontend.weekdays.closed');

            if (!array_get($data, 'enable', false) || empty($hourData = array_get($data, 'hours', []))) return $businessHour;

            $hours = [];
            array_walk($hourData, function($item) use (&$hours,$timezone) {
                if (!is_array($item)) return;
                extract($item);
                $open = isset($open) ? Carbon::parse($open)->setTimeZone($timezone)->format('H:i A') : '';
                $close = isset($close) ? Carbon::parse($close)->setTimeZone($timezone)->format('H:i A') : '';
                $hours[$open] = $close;
            });

            $businessHour['hours'] = $hours;

            return $businessHour;
        })->toArray();

        return $businessHours;
    }
}
