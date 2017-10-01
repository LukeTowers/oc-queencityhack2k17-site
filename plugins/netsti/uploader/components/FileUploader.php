<?php namespace NetSTI\Uploader\Components;

use Input;
use Request;
use System\Models\File;
use ApplicationException;
use Cms\Classes\ComponentBase;
use System\Classes\CombineAssets;
use NetSTI\Uploader\Classes\ModelsAttach;
use RainLab\Builder\Classes\ComponentHelper;

class FileUploader extends ComponentBase
{

    use \NetSTI\Uploader\Traits\ComponentUtils;

    public $maxSize;
    public $placeholderText;

    /**
     * Supported file types.
     * @var array
     */
    public $fileTypes;

    /**
     * @var bool Has the model been bound.
     */
    protected $isBound = false;

    /**
     * @var bool Is the related attribute a "many" type.
     */
    public $isMulti = false;

    /**
     * @var Collection
     */
    public $fileList;

    /**
     * @var Model
     */
    public $singleFile;

    public function componentDetails()
    {
        return [
            'name'        => 'File Uploader',
            'description' => 'Upload a file'
        ];
    }

    public function defineProperties()
    {
        return [
            'placeholderText' => [
                'title'       => 'Placeholder text',
                'description' => 'Wording to display when no file is uploaded',
                'default'     => 'Click or drag files to upload',
                'type'        => 'string',
            ],
            'maxSize' => [
                'title'       => 'Max file size (MB)',
                'description' => 'The maximum file size that can be uploaded in megabytes.',
                'default'     => '5',
                'type'        => 'string',
            ],
            'fileTypes' => [
                'title'       => 'Supported file types',
                'description' => 'File extensions separated by commas (,) or star (*) to allow all types.',
                'default'     => '*',
                'type'        => 'string',
            ],
            'modelClass' => [
                'title'       => 'Model Class',
                'type'        => 'dropdown',
                'group' => 'Model',
                'showExternalParam' => false,
                'placeholder' => '- - - -'
            ],
            'modelKeyColumn' => [
                'title'       => 'Model key column',
                'description' => 'Model column to use as a record identifier for fetching the record from the database.',
                'type'        => 'dropdown',
                'depends'   => ['modelClass'],
                'group' => 'Model',
                'validation'  => [
                    'required' => [
                        'message' =>'The key column name is required'
                    ]
                ],
                'showExternalParam' => false
            ],
            'identifierValue' => [
                'title'       => 'Identifier value',
                'description' => 'Identifier value to load the record from the database. Specify a fixed value or URL parameter name.',
                'type'        => 'string',
                'default'     => '{{ :id }}',
                'group' => 'Model',
                'validation'  => [
                    'required' => [
                        'message' => 'The identifier value is required'
                    ]
                ]
            ],
            'deferredBinding' => [
                'title'       => 'Use deferred binding',
                'description' => 'If checked the associated model must be saved for the upload to be bound.',
                'type'        => 'checkbox',
                'group' => 'Model',
            ],
        ];
    }

    public function init()
    {
        $this->fileTypes = $this->processFileTypes(true);
        $this->maxSize = $this->property('maxSize');
        $this->placeholderText = $this->property('placeholderText');

        // BIND
        $modelClassName = $this->property('modelClass');
        $model = new $modelClassName();
        $bind = $this->property('deferredBinding') ? $model : $model->where('id', $this->property('identifierValue'))->first();
        $this->bindModel($this->property('modelKeyColumn'), $bind);
    }

    public function onRun()
    {
        $this->addCss('assets/css/uploader.css');
        $this->addJs('assets/vendor/dropzone/dropzone.js');
        $this->addJs('assets/js/uploader.js');

        if ($result = $this->checkUploadAction()) {
            return $result;
        }

        $this->fileList = $fileList = $this->getFileList();
        $this->singleFile = $fileList->first();
    }

    public function onRender()
    {
        if (!$this->isBound) {
            throw new ApplicationException('There is no model bound to the uploader!');
        }

        if ($populated = $this->property('populated')) {
            $this->setPopulated($populated);
        }
    }

    /**
     * Adds the bespoke attributes used internally by this widget.
     * - thumbUrl
     * - pathUrl
     * @return System\Models\File
     */
    protected function decorateFileAttributes($file){
        $file->pathUrl = $file->thumbUrl = $file->getPath();

        return $file;
    }

    public function onRemoveAttachment(){
        if (($file_id = post('file_id')) && ($file = File::find($file_id))) {
            $this->model->{$this->attribute}()->remove($file, $this->getSessionKey());
        }
    }

    public function getModelClassOptions(){
        return ModelsAttach::getModelsWithAttach();
    }

    public function getModelKeyColumnOptions(){
        $model = Request::input('modelClass');
        $properties = ModelsAttach::getModelsWithProperties();

        if(array_key_exists($model, $properties))
        	return $properties[$model];
        else
        	return [];
    }

}