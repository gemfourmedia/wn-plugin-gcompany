<?php namespace GemFourMedia\GCompany\Models;

use Model;

/**
 * Model
 */
class Setting extends Model
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;
    
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'gcompany-info';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    // Return default format in case user not setting yet.
    public function getFullAddressFormatAttribute()
    {
    	$defaultFormat = '{{address_1}}, {{address_2}}, {{city}}, {{state}}, {{zip}}, {{country}}';
    	return self::get('full_address_format', $defaultFormat);
    }

    // Return default format in case user not setting yet.
    public function getShortAddressFormatAttribute()
    {
    	$defaultFormat = '{{address_1}}, {{address_2}}, {{city}}, {{state}}, {{zip}}';
    	return self::get('short_address_format', $defaultFormat);
    }
}
