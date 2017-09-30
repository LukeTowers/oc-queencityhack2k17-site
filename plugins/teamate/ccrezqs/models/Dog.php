<?php namespace TeamAte\Ccrezqs\Models;

use Model;

/**
 * Model
 */
class Dog extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    protected $jsonable = ['medical_info', 'general_info'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'teamate_ccrezqs_dogs';


    public $morphMany = [
        'notes' => ['TeamAte\Ccrezqs\Models\Note', 'name' => 'target']
    ];
}
