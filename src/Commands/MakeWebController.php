<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeWebController extends Command
{
    use AssistCommand;

    protected $signature = 'create:web-controller
        {name : The name of the model }?
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new web controller';

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Handle the command
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        $this->createDashboardController($modelName, $actor);
    }

    /**
     * @throws FileNotFoundException
     */
    private function createDashboardController($name, array $attributeType)
    {
        $modelName = ucfirst($name);

        $modelPluralName = Str::plural(strtolower($modelName));

        //create controller
        $stub = $this->getStub('controller.dashboard.stub');
        $stub = $this->replace('{{class}}', $modelName, $stub);
        $stub = $this->replace('{{modelPluralName}}', $modelPluralName, $stub);
        $stub = $this->putVariablesInIndexFunction($attributeType, $stub);
        $this->putImageLogicIfExists($attributeType, Str::lower($modelName), $stub);
        $this->files->put(app_path("Http/Modules/$modelName/Ui/Web/Controllers/".$modelName.'Controller.php'), $stub);
        $this->info("Controller $modelName created successfully!");
        //create datatables views
        $this->files->makeDirectory(resource_path("views/dashboard/$modelPluralName"));
        $stub = $this->getStub('datatables/table.stub');
        $stub = $this->replace('{{class}}', $modelName, $stub);
        $stub = $this->replace('{{modelPluralName}}', $modelPluralName, $stub);
        $stub = $this->replaceColsHeader($attributeType, $stub);
        $stub = $this->replaceColsData($attributeType, $stub);
        $this->files->put(resource_path("views/dashboard/$modelPluralName/index.blade.php"), $stub);
        $this->info("Table View $modelName created successfully!");
        $wayBuild = $this->choice(
            'What is the way to create the interfaces you want',
            ['classic', 'modern'],
            0
        );
        //get form fields to add to the form
        if ($wayBuild == 'classic') {
            $createFormFields = $this->generateFormInputs($attributeType, 'create');
            $updateFormFields = $this->generateFormInputs($attributeType, 'edit');
            $createStub = $this->getStub('views/create.blade.stub');
            $updateStub = $this->getStub('views/edit.blade.stub');
            $createStub = str_replace('{{modelPluralName}}', Str::plural(strtolower($name)), $createStub);
            $updateStub = str_replace('{{modelPluralName}}', Str::plural(strtolower($name)), $updateStub);
            $createStub = $this->replaceFields($createFormFields, $createStub);
            $updateStub = $this->replaceFields($updateFormFields, $updateStub);
            $this->files->put(resource_path("views/dashboard/$modelPluralName/create.blade.php"), $createStub);
            $this->files->put(resource_path("views/dashboard/$modelPluralName/edit.blade.php"), $updateStub);
        } else {
            $stub = $this->getStub('datatables/form.stub');
            $formFields = $this->generateFormInputs($attributeType, 'create');
            $stub = $this->getStub('datatables/form.stub');
            $stub = $this->replace('{{class}}', $modelName, $stub);
            $stub = $this->replace('//Form Fields', $formFields, $stub);
            $this->files->put(resource_path("views/dashboard/$modelPluralName/form.blade.php"), $stub);
        }
        $this->info("Form View $modelName created successfully!");
        //create routes
    }

    /**
     * @throws FileNotFoundException
     */
    private function getStub($stub_name): string
    {
        return $this->files->get(base_path('stubs/'.$stub_name));
    }

    private function replace($search, $replaceTo, &$stub): array|string
    {
        $stub = str_replace($search, $replaceTo, $stub);

        return $stub;
    }

    private function putVariablesInIndexFunction($attribute, &$stub): array|string
    {
        $setVariables = '';
        $nameSpaces = '';
        $variables = '';
        foreach ($attribute as $key => $type) {
            if ($type == 'key') {
                $modelName = ucfirst(Str::replace('_id', '', $key));
                $setVariables .= '$'.$key.'s='.$modelName."::all();\n\t\t";
                $nameSpaces .= "use App\Models\\$modelName;\n";
                $variables .= ",'{$key}s'";
            }
        }
        $nameSpaces .= '// add namespace';
        $stub = str_replace('{{set variables}}', $setVariables, $stub);
        $stub = str_replace('// add namespace', $nameSpaces, $stub);
        $stub = str_replace('{{variables}}', $variables, $stub);

        return $stub;
    }

    private function putImageLogicIfExists($attribute, $modelName, &$stub)
    {
        $filesColumns = array_keys($attribute, 'file');
        if (count($filesColumns) != 0) {
            $store_logic = '';
            $update_logic = '';
            $nameSpaces = "use App\Traits\FileHandler;\n use App\Http\Requests\\{$modelName}DeleteImageRequest;\n// add namespace";
            $stub = str_replace('// add namespace', $nameSpaces, $stub);
            $stub = str_replace('// set trait', "use FileHandler;\n// set trait ", $stub);
            foreach ($filesColumns as $column) {
                $store_logic .= "\$more_data=[];\n\t\tif(\$request->hasFile('$column')){\n\t\t\t\$more_data['$column']=\$this->storeFile(\$request->$column,'$modelName/{$column}s');\n\t\t}\n\t\t";
                $update_logic .= "\$more_data=[];\n\t\tif(\$request->hasFile('$column')){\n\t\t\t\$more_data['$column']=\$this->updateFile(\$request->$column,\$model->$column,'$modelName/{$column}s');\n\t\t}\n\t\t";

            }
            $stub = str_replace('// set logic store function', $store_logic, $stub);
            $stub = str_replace('// set logic update function', $update_logic, $stub);
            $stub = str_replace('$request->validated()', 'array_merge($request->validated(),$more_data)', $stub);
        }

        return $stub;
    }

    public function generateFormInputs(array $attributes, $action): array|string
    {
        $fields = '';
        foreach ($attributes as $attributeName => $attributeType) {
            $label = Str::title($attributeName);
            $field = $attributeName;
            $fields .= $this->replaceLabelName($label, $field, $this->getInput($attributeType)."\n");
            if ($action == 'create') {
                $fields = str_replace('{{value}}', "{{old('$attributeName')}}", $fields);
            } else {
                $fields = str_replace('{{value}}', "{{\$model->$attributeName}}", $fields);
            }
        }

        return $fields;
    }

    private function replaceLabelName($labelName, $fieldName, $out): array|string
    {
        $labelName = str_replace('_', ' ', $labelName);
        $out = str_replace('LabelName', $labelName, $out);

        return str_replace('fieldName', $fieldName, $out);
    }

    private function getInput($type): string
    {
        return match ($type) {
            'string', 'json' => '<div class="form-group col-6">
                    <label class="required-label">LabelName</label>
                    <input type="text" name="fieldName" class="form-control" value="{{value}}">
                </div>',
            'text' => '<div class="form-group col-6">
                        <label class="required-label">LabelName</label>
                        <textarea class="form-control" name="fieldName">{{value}}</textarea>
                    </div>',
            'integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float' => '<div class="form-group col-6">
                        <label class="required-label">LabelName</label>
                        <input type="number" step="any" name="fieldName" value="{{value}}" class="form-control">
                </div>',
            'boolean' => '<div class="form-group col-6">
                        <label class="d-block">LabelName</label>
                        <input type="checkbox" id="fieldName" switch="primary" value="{{value}}" name="fieldName"/>
                        <label for="fieldName" data-on-label="Yes" data-off-label="No"></label>
                    </div>',
            'date', 'time', 'dateTime', 'timestamp' => '<div class="form-group col-6">
                        <label for="date-format">LabelName</label>
                        <div>
                            <input type="date" name="fieldName" value="{{value}}" class="form-control floating-label date-format" placeholder="Begin Date Time">
                        </div>
                    </div>',
            'file' => '<div class="form-group col-6">
                        <label for="date-format">LabelName</label>
                        <div>
                            <input type="file" name="fieldName" value="{{value}}" class="form-control floating-label date-format" placeholder="Begin Date Time">
                        </div>
                    </div>',
            'key' => '<div class="form-group col-6">
                        <label for="date-format">LabelName</label>
                        <div>
                            <select  name="fieldName"  class="form-control floating-label date-format">
                                @foreach($fieldNames as $fieldName)
                                    <option value="{{$fieldName->id}}">{{ $fieldName->name }}</option>
                                @endforeach()
                            </select>
                        </div>
                    </div>',
            default => '',
        };
    }

    private function replaceColsHeader($cols, &$stub): array|string
    {
        $rows = "\t\t<th>Id</th>\n";
        foreach ($cols as $colName => $colType) {
            $col = ucfirst($colName);
            $rows .= "\t\t<th>$col</th>\n";
        }
        $rows .= "\t\t<th>Actions</th>\n";
        $stub = str_replace('{{colsHeader}}', $rows, $stub);

        return $stub;
    }

    private function replaceColsData($cols, &$stub): array|string
    {
        $rows = "\t\t{\"data\": \"id\"},\n";
        foreach ($cols as $colName => $colType) {
            $rows .= "\t\t{\"data\": \"$colName\"},\n";
        }
        $rows .= "{data: 'action', name: 'action', orderable: false, searchable: false}";
        $stub = str_replace('{{colsData}}', $rows, $stub);

        return $stub;
    }

    private function replaceFields($formFields, &$stub): array|string
    {
        $stub = str_replace('//Form Fields', $formFields, $stub);

        return $stub;
    }
}
