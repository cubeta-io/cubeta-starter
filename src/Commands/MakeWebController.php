<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeWebController extends Command
{
    use AssistCommand;
    use RouteBinding;

    protected $signature = 'create:web-controller
        {name : The name of the model }
        {attributes : the model attributes}
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new web controller';

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor');
        $attributes = $this->argument('attributes');

        $modelName = $this->modelNaming($name);

        $this->createWebController($modelName, $attributes, $actor);
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, array $attributes, $actor = null)
    {
        $modelNameLower = strtolower($modelName);

        $controllerName = $modelName . 'Controller';
        $controllerPath = base_path('app/Http/Controllers/WEB/v1/' . $controllerName . '.php');

        if (file_exists($controllerPath)) {
            $this->line("<info>The Controller $controllerName <fg=red>Already Exists</fg=red></info>");
            return;
        }

        $modelLowerPluralName = strtolower(Str::plural($modelName));
        $routesNames = $this->getRoutesNames($modelName, $actor);
        $views = $this->getViewsNames($modelName, $actor);

        $this->generateCreateForm($modelName, $routesNames['store'], $attributes);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelLowerName}' => $modelNameLower,
            '{modelLowerPluralName}' => $modelLowerPluralName,
            '{indexRouteName}' => $routesNames['index'],
            '{showRouteName}' => $routesNames['show'],
            '{editRouteName}' => $routesNames['edit'],
            '{deleteRouteName}' => $routesNames['destroy'],
            '{createForm}' => $views['create'],
            '{indexView}' => $views['index'],
            '{showView}' => $views['show'],
            '{editForm}' => $views['edit'],
            '{columns}' => $this->generateDataTableColumns($attributes, $modelNameLower)
        ];

        if (!is_dir(base_path('app/Http/Controllers/WEB/v1/'))) {
            mkdir(base_path('app/Http/Controllers/WEB/v1/'), 0777, true);
        }

        new CreateFile(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.web.stub'
        );

        $this->line("<info> $controllerName Created </info>");
        $this->addRoute($modelName, $actor, 'web');
    }

    /**
     * @param string $modelName
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => "string", 'edit' => "string", 'create' => "string", 'show' => "string"])]
    public function getViewsNames(string $modelName, $actor = null): array
    {
        $modelLowerPluralName = strtolower(Str::plural($modelName));
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return [
                'index' => 'dashboard.' . $modelLowerPluralName . '.index',
                'edit' => 'dashboard.' . $modelLowerPluralName . '.edit',
                'create' => 'dashboard.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $modelLowerPluralName . '.show',
            ];
        } else {
            return [
                'index' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.index',
                'edit' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.edit',
                'create' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.show',
            ];
        }
    }

    /**
     * generate the data tables columns query
     * @param array $attributes
     * @param string $modelNameLower
     * @return string
     */
    public function generateDataTableColumns(array $attributes, string $modelNameLower): string
    {
        $columns = '';
        foreach ($attributes as $attribute => $key) {
            $columns .= "->addColumn('name', function (\$$modelNameLower) {
                return \$$modelNameLower->$attribute;
            })
            ->filterColumn('$attribute', function (\$query,\$keyword) {
                    \$query->where('$attribute', 'like', \" % \$keyword % \");
                })
            ";
        }
        return $columns;
    }

    /**
     * @param string $modelName
     * @param $actor
     * @return string[]
     */
    #[ArrayShape(['index' => "string", 'show' => "string", 'edit' => "string", 'destroy' => "string", 'store' => "string"])]
    public function getRoutesNames(string $modelName, $actor = null): array
    {
        $baseRouteName = $this->getRouteName($modelName, 'web', $actor);
        return [
            'index' => $baseRouteName . 'index',
            'show' => $baseRouteName . 'show',
            'edit' => $baseRouteName . 'edit',
            'destroy' => $baseRouteName . 'destroy',
            'store' => $baseRouteName . 'store',
        ];
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateCreateForm(string $modelName, string $storeRoute, array $attributes)
    {
        $lowerPluralModelName = strtolower(Str::plural($modelName));
        $inputs = $this->generateInputs($attributes);
        $stubProperties = [
            "{modelName}" => $modelName,
            "{storeRoute}" => $storeRoute,
            "{components}" => $inputs
        ];

        $formDirectory = base_path("resources/views/$lowerPluralModelName/create.blade.php");

        if (!is_dir(base_path("resources/views/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($formDirectory)) {
            $this->line("<info>Create Form Already Created</info>");
            return;
        }

        new CreateFile(
            $stubProperties,
            $formDirectory,
            __DIR__ . "/stubs/views/form.stub"
        );

        $this->line("<info>A create form for $lowerPluralModelName created</info>");
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function generateInputs(array $attributes): string
    {
        $inputs = '';
        foreach ($attributes as $attribute => $type) {
            $label = $this->getInputLabel($attribute);

            if ($attribute == 'email') {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"email\"></x-forms.input> \n";
                continue;
            }

            if ($attribute == 'password') {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"password\"></x-forms.input> \n";
                continue;
            }

            if (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"tel\"></x-forms.input> \n";
                continue;
            }

            if (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"url\"></x-forms.input> \n";
                continue;
            }

            if (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"number\"></x-forms.input> \n";
            }

            if (in_array($type, ['string', 'json'])) {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"text\"></x-forms.input> \n";
            }

            if ($type == 'text') {
                $inputs .= "\n <x-forms.texteditor label=\"$label\"></x-forms.texteditor> \n";
            }

            if ($type == 'date') {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"date\"></x-forms.input> \n";
            }

            if ($type == 'time') {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"time\"></x-forms.input> \n";
            }

            if (in_array($type, ['dateTime', 'timestamp'])) {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"datetime-local\"></x-forms.input> \n";
            }

            if ($type == 'file') {
                $inputs .= "\n <x-forms.input label=\"$label\" type=\"file\"></x-forms.input> \n";
            }
        }

        return $inputs;
    }

    /**
     * @param string $attribute
     * @return array|string
     */
    public function getInputLabel(string $attribute): array|string
    {
        return str_replace('_', ' ', Str::title(Str::singular($attribute)));
    }
}
