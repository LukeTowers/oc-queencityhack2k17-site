<?php namespace TeamAte\Ccrezqs\Models;

use Auth;
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

    public $belongsTo = [
        'foster' => ['RainLab\User\Models\User'],
    ];

    public $morphMany = [
        'notes' => ['TeamAte\Ccrezqs\Models\Note', 'name' => 'target']
    ];

    public $attachMany = [
        'photos' => 'System\Models\File'
    ];


    public function scopeCurrentUser($query)
    {
        $userId = ($user = Auth::getUser()) ? $user->id : 0;
        return $query->where('foster_id', $userId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('foster_id', null);
    }
}
