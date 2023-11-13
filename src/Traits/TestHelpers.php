<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait TestHelpers
{
    use RefreshDatabase;

    protected $model;

    protected $relations = [];

    protected $requestPath;

    protected $resource;

    protected $user;

    protected $userType;

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
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        if (isset($this->userType) && $this->userType != 'none') {
            $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
            Artisan::call('db:seed PermissionSeeder');
            Artisan::call('db:seed RoleSeeder');
        }

        $this->signIn($this->userType);
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
     * this function is for converting the return value of a resource as an array
     *
     * @param mixed $data     the data that  has to be converted
     * @param bool  $multiple if you want to return an array of data
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
     * @param  string $routeName the route name
     * @return void
     */
    public function requestPathHook(string $routeName = ''): void
    {
        $this->requestPath = $routeName;
    }

    public function signIn($type = null): void
    {
        $this->user = User::factory()->create();
        if (isset($type) && $type != 'none') {
            $this->user->assignRole($type);
        }
        $this->be($this->user);
    }
}
