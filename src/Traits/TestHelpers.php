<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

trait TestHelpers
{
    use RefreshDatabase;

    protected string $model;

    protected array $relations = [];

    protected string $requestPath;

    protected string $resource;

    protected User $user;

    protected string $userType;

    protected string $baseUrl;

    protected array $pagination = [
        'currentPage' => 1,
        'from' => 1,
        'to' => 5,
        'total' => 5,
        'per_page' => 10,
    ];

    protected array $responseBody = [
        'data' => null,
        'status' => true,
        'code' => 200,
        'paginate' => null,
    ];

    /**
     * data = false ||| message  = there is no data
     */
    public function failedFalseResponse(): void
    {
        $this->responseBody['data'] = false;
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    /**
     * data = [] ||| message = there is no data
     *
     * @return void
     */
    public function failedMultiResponse()
    {
        $this->responseBody['data'] = [];
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    /**
     * this function for login using email address and default password is 12345678
     */
    public function login(string $email, string $password = '12345678'): void
    {
        auth()->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * @param string $routeName the route name
     * @return void
     */
    public function requestPathHook(string $routeName = ''): void
    {
        $this->requestPath = $routeName;
    }

    /**
     * initialize the required values
     * @param string $model
     * @param string $resource
     * @param string $baseUrl
     * @param string $userType
     * @param array $relations
     * @return void
     */
    public function initialize(string $model, string $resource, string $baseUrl, string $userType = 'none', array $relations = []): void
    {
        $this->model = $model;

        $this->resource = $resource;

        // define the actor
        $this->userType = $userType;

        // the named route eg: 'user.products.'
        //!!! Note: the dot "." in the end of the baseUrl is important !!!
        $this->baseUrl = $baseUrl;

        // if your endpoints return the model with its relation put the relations in the array
        $this->relations = $relations;


        if (isset($this->userType) && $this->userType != 'none') {
            $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
            Artisan::call('db:seed PermissionSeeder');
            Artisan::call('db:seed RoleSeeder');
        }

        $this->signIn($this->userType);
    }

    public function signIn($type = null): void
    {
        $this->user = User::factory()->create();
        if (isset($type) && $type != 'none') {
            $this->user->assignRole($type);
        }
        $this->be($this->user);
    }

    /**
     * @param array $additionalFactoryData optional data to the factories
     * @param bool $ownership determine if the action has to be on the authenticated user data
     */
    public function deleteTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false): void
    {
        // the user provided undefined ID
        $this->responseBody['data'] = false;
        $this->responseBody['message'] = __('site.there_is_no_data');

        $this->delete(route($this->requestPath, fake()->uuid()))
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($ownership) {
            // the user tried to delete another user data
            $array = array_merge($additionalFactoryData, ['user_id' => User::factory()->create()->id]);
            $factoryData = $this->model::factory()->create($array);

            $this->delete(route($this->requestPath, $factoryData->id))
                ->assertExactJson($this->responseBody)
                ->assertOk();

            // the user tried to delete his data
            $array = array_merge($additionalFactoryData, ['user_id' => auth()->user()->id]);
            $factoryData = $this->model::factory()->create($array);
        } else {
            $factoryData = $this->model::factory()->create($additionalFactoryData);
        }

        $this->responseBody['data'] = true;
        $this->responseBody['message'] = __('site.delete_successfully');

        $response = $this->delete(route($this->requestPath, $factoryData->id))
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($this->checkSoftDeleteColumn()) {
            $this->assertSoftDeleted($factoryData);
        } else {
            $this->assertModelMissing($factoryData);
        }

        if ($isDebug) {
            dd($response);
        }
    }

    /**
     * check if the model can soft-delete
     */
    public function checkSoftDeleteColumn(): bool
    {
        $tableName = (new $this->model())->getTable();
        $columns = Schema::getColumnListing($tableName);

        return (bool)(in_array('deleted_at', $columns));
    }

    /**
     * @param array $additionalFactoryData optional data to the factories
     * @param bool $ownership determine if the action has to be on the authenticated user data
     */
    public function indexTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false): void
    {
        // there is no data
        $this->responseBody['data'] = [];
        $this->responseBody['message'] = __('site.there_is_no_data');

        $this->get(route($this->requestPath))
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($ownership) {
            // the user tried to index another user data
            $array = array_merge($additionalFactoryData, ['user_id' => User::factory()->create()->id]);
            $this->model::factory(5)->create($array);

            $this->get(route($this->requestPath))
                ->assertExactJson($this->responseBody)
                ->assertOk();

            // the user tried to index his own data
            $array = array_merge($additionalFactoryData, ['user_id' => auth()->user()->id]);
            $factoryData = $this->model::factory(5)->create($array);
        } else {
            // data exists
            $factoryData = $this->model::factory(5)->create($additionalFactoryData);
        }

        $this->responseBody['data'] = $this->convertResourceToArray($factoryData->load($this->relations), true);
        $this->responseBody['message'] = __('site.get_successfully');
        $this->responseBody['paginate'] = $this->pagination;
        $response = $this->get(route($this->requestPath))
            ->assertExactJson($this->responseBody)
            ->assertOk();
        if ($isDebug) {
            dd($response);
        }
    }

    /**
     * this function is for converting the return value of a resource as an array
     *
     * @param mixed $data the data that  has to be converted
     * @param bool $multiple if you want to return an array of data
     */
    public function convertResourceToArray(mixed $data, bool $multiple = false): array
    {
        if (!$multiple) {
            return json_decode(
                json_encode(new $this->resource($data)),
                JSON_PRETTY_PRINT
            );
        }

        return json_decode(
            json_encode($this->resource::collection($data)),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * @param array $additionalFactoryData optional data to the factories
     * @param bool $ownership determine if the action has to be on the authenticated user data
     */
    public function showTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false): void
    {
        // the user provided invalid id
        $this->responseBody['message'] = __('site.there_is_no_data');

        $this->get(route($this->requestPath, fake()->uuid()))
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($ownership) {
            // the user tried to show another user data
            $array = array_merge($additionalFactoryData, ['user_id' => User::factory()->create()->id]);
            $factoryData = $this->model::factory()->create($array);

            $this->get(route($this->requestPath, $factoryData->id))
                ->assertExactJson($this->responseBody)
                ->assertOk();

            // the user tried to show his own data
            $array = array_merge($additionalFactoryData, ['user_id' => auth()->user()->id]);
            $factoryData = $this->model::factory()->create($array);
        } else {
            $factoryData = $this->model::factory()->create($additionalFactoryData);
        }

        // the user provided the right id
        $this->responseBody['data'] = $this->convertResourceToArray($factoryData->load($this->relations));
        $this->responseBody['message'] = __('site.get_successfully');
        $response = $this->get(route($this->requestPath, $factoryData->id))
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($isDebug) {
            dd($response);
        }
    }

    /**
     * @param array $additionalAttributes optional data to the factories
     */
    public function storeTest(array $additionalAttributes = [], mixed $requestParams = null, bool $isDebug = false): void
    {
        $attributes = $this->model::factory()->raw($additionalAttributes);
        $response = $this->post(route($this->requestPath, $requestParams), $attributes);

        if ($isDebug) {
            dd($response);
        }

        $createdModel = $this->model::orderByDesc('id')->first();

        $this->responseBody['data'] = $this->convertResourceToArray($createdModel->load($this->relations));
        $this->responseBody['message'] = __('site.stored_successfully');

        $response->assertExactJson($this->responseBody)
            ->assertOk();

        $this->assertModelExists($createdModel);
    }

    /**
     * @param array $additionalFactoryData optional data to the factories
     * @param array $attributes if you are trying to send a custom attributes to the update request send an array of it
     * @param bool $ownership determine if the action has to be on the authenticated user data
     * @param bool $replacing this var pointing to the case where the update endpoint creating a new record to the database
     */
    public function updateTest(array $attributes = [], array $additionalFactoryData = [], bool $ownership = false, bool $replacing = true, bool $isDebug = false): void
    {
        $attributes = $this->model::factory()->raw($attributes);

        //the user provided invalid ID
        $this->responseBody['message'] = __('site.there_is_no_data');
        $this->put(route($this->requestPath, fake()->uuid), $attributes)
            ->assertExactJson($this->responseBody)
            ->assertOk();

        if ($ownership) {
            // the user tried to update another user data
            $array = array_merge($additionalFactoryData, ['user_id' => User::factory()->create()->id]);
            $factoryData = $this->model::factory()->create($array);

            $this->get(route($this->requestPath, $factoryData->id))
                ->assertExactJson($this->responseBody)
                ->assertOk();

            // the user tried to update his data
            $array = array_merge($additionalFactoryData, ['user_id' => auth()->user()->id]);
            $factoryData = $this->model::factory()->create($array);
        } else {
            $factoryData = $this->model::factory()->create($additionalFactoryData);
        }

        //the user provided the right ID
        $response = $this->put(route($this->requestPath, $factoryData->id), $attributes)->assertOk();
        if ($replacing) {
            $factoryData->refresh();
            $this->assertModelExists($factoryData);
            $this->responseBody['data'] = $this->convertResourceToArray($factoryData->load($this->relations));
        } else {
            $createdData = $this->model::query()->orderByDesc('id')->first();
            $this->assertModelExists($createdData);
            $this->responseBody['data'] = $this->convertResourceToArray($createdData->load($this->relations));
        }
        $this->responseBody['message'] = __('site.update_successfully');
        $response->assertExactJson($this->responseBody);

        if ($isDebug) {
            dd($response);
        }
    }
}
