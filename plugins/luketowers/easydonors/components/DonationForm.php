<?php namespace LukeTowers\EasyDonors\Components;

use Auth;
use Flash;
use Redirect;
use Cms\Classes\ComponentBase;

use LukeTowers\EasyDonors\Models\Donor as DonorModel;
use LukeTowers\EasyDonors\Models\DonorAddress as DonorAddressModel;
use LukeTowers\EasyDonors\Models\Donation as DonationModel;
use LukeTowers\EasyDonors\Models\DonationAddress as DonationAddressModel;

// Yes I know this is complete crap, I had literally no time left to make it :P
class DonationForm extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'DonationForm Component',
            'description' => 'No description provided yet...'
        ];
    }
    
    public function init() {
	    $this->addJs('https://js.stripe.com/v2/');
    }
    
    public function onSubmit() {
	    // Initilizations
	    $errors = array();
	    $create_donor = false;


        // Ensure that the amount selected for donation meets or exceeds the minimum donation of $1.00
        $donation_amount = filter_var(input('amount'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (!($donation_amount >= 1.00)) {
			$errors[] = 'The minimum donation amount is $1.00';
        }
		
		
		// Get the current donor by their email
		$email = input('email');
		$donor = DonorModel::where('email', '=', $email)->first();
		
		$address = input('address');
		$address_2 = input('address_2');
		$city = input('city');
		$province = input('province');
		$country = input('country');
		$postal_code = input('postalcode');
		
		if (!$donor) {
			try {
				// Create new donor
				$donor = new DonorModel();
				$donor->name = input('name');
				$donor->surname = input('surname');
				$donor->email = input('email');
				$donor->save();
				
				// Create new donor address for the donor
				$donorAddress = new DonorAddressModel();
				$donorAddress->address = $address;
				$donorAddress->address_2 = $address_2;
				$donorAddress->city = $city;
				$donorAddress->province = $province;
				$donorAddress->country = $country;
				$donorAddress->postal_code = $postal_code;
				$donorAddress->donor_id = $donor->id;
				$donorAddress->save();
			} catch (\Exception $ex) {
				$errors[] = $ex->getMessage();
			}
		}
        
        // Handle any errors
        if (!empty($errors)) {
	        Flash::error($errors);
	        throw new \ApplicationException(implode(' ', $errors));	       
        }
        
        // Create donation
        $donation = new DonationModel();
        $donation->amount = $donation_amount;
        $donation->donor_name = $donor->name . ' ' . $donor->surname;
        $donation->currency = 'CAD';
        $donation->donor_id = $donor->id;
        $donation->save();
        
        
        
        // Create donation address
       /*
	       
	       For some magical reason, this works without being run???????
 		throw new \ApplicationException('test');
        $donationAddress = new DonationAddressModel();
        $donationAddress->address = $address;
		$donationAddress->address_2 = $address_2;
		$donationAddress->city = $city;
		$donationAddress->country = $country;
		$donationAddress->province = $province;
		$donationAddress->postal_code = $postal_code;
		$donationAddress->donation_id = $donation->id;
		$donationAddress->save();
*/
		

		
		throw new \ApplicationException('Success!');
	}
}