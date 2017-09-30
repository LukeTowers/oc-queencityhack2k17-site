<?php namespace LukeTowers\EasyDonors\Models;

use Model;

/**
 * Model
 */
class DonorAddress extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
	    'address'     => 'required',
	    'city'        => 'required',
	    'province'    => 'required',
	    'country'     => 'required',
	    'postal_code' => 'required',
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = ['data'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'luketowers_easydonors_donor_addresses';
    
    /*
	 * Relations
	 */
	public $belongsTo = [
		'donor' => ['LukeTowers\EasyDonors\Models\Donor', 'key' => 'donor_id'],
	];
	
	
	public function getCountryOptions($key, $value) {
		return [
			'CA' => 'Canada',
			'US' => 'United States',
		];
	}
	
	public function getProvinceOptions($key, $value) {
		return [
			'SK'	=>	'Saskatchewan', // Default for hackathon; TODO: Clean up, provide smarter way to provide regions based on country selection
			'AB'	=>	'Alberta',
			'BC'	=>	'British Columbia',
			'MB'	=>	'Manitoba',
			'NB'	=>	'New Brunswick',
			'NL'	=>	'Newfoundland & Labrador',
			'NS'	=>	'Nova Scotia',
			'NT'	=>	'Northwest Territories',
			'NU'	=>	'Nunavut',
			'ON'	=>	'Ontario',
			'PE'	=>	'Prince Edward Island',
			'QC'	=>	'Quebec',
			'YT'	=>	'Yukon',
		];
	}
}