<?php namespace TeamAte\Ccrezqs\Models;

use Model;

/**
 * Model
 */
class Note extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'teamate_ccrezqs_notes';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /*
     * Validation
     */
    public $rules = [
        'contents' => ['required']
    ];

    public $morphTo = [
        'target' => []
    ];

    /**
     * Disable setting the updated_at column automatically as this model doesn't support that column
     *
     * @param mixed $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }
}
