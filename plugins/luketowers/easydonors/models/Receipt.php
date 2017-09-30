<?php namespace LukeTowers\EasyDonors\Models;

use Model;

/**
 * Model
 */
class Receipt extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = ['data'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'luketowers_easydonors_receipts';
    
    /*
	 * Relations
	 */
	public $belongsTo = [
		'donation' => ['LukeTowers\EasyDonors\Models\DonationAddress', 'key' => 'donation_id'],
	];
}