<?php namespace LukeTowers\EasyDonors\Models;

use Model;
use Carbon;
use ApplicationException;
use LukeTowers\EasyDonors\Models\DonationAddress as DonationAddressModel;

/**
 * Model
 */
class Donation extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
	    'amount' => 'required',
	    'currency' => 'required',
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = ['data'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'luketowers_easydonors_donations';
    
    /*
	 * Relations
	 */
	public $belongsTo = [
		'donor' => ['LukeTowers\EasyDonors\Models\Donor', 'key' => 'donor_id'],
	];
	
    public $hasOne  = [
	    'receipt' => ['LukeTowers\EasyDonors\Models\Receipt', 'key' => 'donation_id'],
	    'address' => ['LukeTowers\EasyDonors\Models\DonationAddress', 'key' => 'donation_id'],
    ];
    
    public function getCurrencyOptions() {
	    return [
		    'CAD' => 'Canadian dollars (CAD)',
		    'USD' => 'American dollars (USD)',
	    ];
    }
    
    public function beforeSave() {
	    if (empty($this->donor_name)) {
		    $this->attributes['donor_name'] = $this->donor->getFullName();
	    }
	    
	    if (empty($this->date)) {
		    $this->attributes['date'] = Carbon\Carbon::now()->toDateTimeString();
	    }
    }
    
    public function afterSave() {
	    // NOTE: afterSave gets called twice for some stupid reason without reloading the relationships the second time round, look into implementing this functionality a different way as we're forced to manually refresh the relationship checking to see if it exists
	    $this->load('address');
	    if (!count($this->address)) {
		    if (count($this->donor->address)) {
			    DonationAddressModel::createFromDonorAddress($this->donor->address, $this);
		    } else {
			    throw new ApplicationException('No address to pull from');
		    }
	    }
    }
}