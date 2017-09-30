<?php namespace TeamAte\Ccrezqs;

use File;
use Yaml;
use System\Classes\PluginBase;

use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UserController;

class Plugin extends PluginBase
{
    public $requires = ['RainLab.User'];

    public function boot()
    {
        UserModel::extend(function ($model) {
            // Setup Relationships
            $model->morphMany = ['notes' => ['TeamAte\Ccrezqs\Models\Note', 'name' => 'target']];
            $model->hasMany['dogs'] = 'TeamAte\CcRezqs\Models\Dog';

            $model->jsonable(['contact_info', 'residence', 'personal_info', 'animal_info', 'foster_info', 'other_info']);

            $model->addFillable(['contact_info', 'residence', 'personal_info', 'animal_info', 'foster_info', 'other_info']);
        });

        UserController::extend(function ($controller) {
            $controller->addDynamicProperty('relationConfig', '$/teamate/ccrezqs/controllers/dogs/config_relation.yaml');
            $controller->relationConfig = '$/teamate/ccrezqs/controllers/dogs/config_relation.yaml';

            $controller->implement[] = 'Backend.Behaviors.RelationController';
        });

        UserController::extendFormFields(function ($form, $model, $context) {
            if (!($model instanceof UserModel)) {
                return;
            }

            $form->removeField('groups');
            $form->removeField('email');
            $form->removeField('block_mail');

            $fields = Yaml::parse(File::get(plugins_path('teamate/ccrezqs/models/foster/fields.yaml')));

            $form->addTabFields($fields['fields']);
        });
    }

    public function registerComponents()
    {
        return [
            'TeamAte\Ccrezqs\Components\ApplicationFoster' => 'applicationFoster',
            'TeamAte\Ccrezqs\Components\PetsListing'  => 'petsList'
        ];
    }
}
