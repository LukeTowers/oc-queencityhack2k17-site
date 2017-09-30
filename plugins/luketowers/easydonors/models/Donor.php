<?php namespace LukeTowers\EasyDonors\Models;

use Model;

/**
 * Model
 */
class Donor extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
	    'name' => 'required',
	    'surname' => 'required',
	    'email' => 'required',
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = ['data'];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'luketowers_easydonors_donors';
    
    
    /*
	 * Relations
	 */
	public $belongsTo = [
		'user' => ['RainLab\User\Models\User', 'key' => 'user_id'],
	];
    public $hasOne  = [
	    'address' => ['LukeTowers\EasyDonors\Models\DonorAddress', 'key' => 'donor_id'],
    ];
    public $hasMany = [
	    'donations' => ['LukeTowers\EasyDonors\Models\Donation', 'key' => 'donor_id'],
    ];
	// TODO: Verify that this relationship actually works
    public $hasManyThrough = [
	    'receipts' => ['LukeTowers\EasyDonors\Models\Receipt', 'through' => 'LukeTowers\EasyDonors\Models\Donation'],
    ];
    
    
    
    public function setOrCreateAttribute($attribute, $value) {
	    $this->attributes = array_merge($this->attributes, [$attribute => $value]);
    }
    
    public function getFullName() {
	    return $this->name . ' ' . $this->surname;
    }
    
    // TODO: Add support for currencies
    public function getTotalDonationsAttribute() {
	    return '$' . number_format($this->donations->sum('amount'), 2);
    }
    
    public function getAvgDonationsAttribute() {
	    return '$' . number_format($this->donations->avg('amount'), 2);
    }
    
    /*
	 * Mutators for accessing connected user object values instead of this object's built in values
	 */
    public function getNameAttribute($value) {
	    if (count($this->user)) {
		    return $this->user->name;
	    } else {
		    return $value;
	    }
    }
    
    public function getSurnameAttribute($value) {
	    if (count($this->user)) {
		    return $this->user->surname;
	    } else {
		    return $value;
	    }
    }
    
    public function getEmailAttribute($value) {
	    if (count($this->user)) {
		    return $this->user->email;
	    } else {
		    return $value;
	    }
    }
    
    public function setNameAttribute($value) {
	    if (count($this->user)) {
		    $this->user->name = $value;
		}
		$this->setOrCreateAttribute('name', $value);
    }
    
    public function setSurnameAttribute($value) {
	    if (count($this->user)) {
		    $this->user->surname = $value;
		}
		$this->setOrCreateAttribute('surname', $value);
    }
    
    public function setEmailAttribute($value) {
	    if (count($this->user)) {
		    $this->user->email = $value;
		}
		$this->setOrCreateAttribute('email', $value);
    }
}