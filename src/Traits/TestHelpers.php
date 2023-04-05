<?php

namespace Cubeta\CubetaStarter\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

trait TestHelpers
{
    use RefreshDatabase;

    protected $user;

    protected $userType = null;

    protected $model;

    protected $resource;

    protected $requestPath;

    protected $relations = [];

    protected array $responseBody = [
        'data' => null,
        'status' => true,
        'code' => 200,
        'paginate' => null,
    ];

    protected array $pagination = [
        'currentPage' => 1,
        'from' => 1,
        'to' => 5,
        'total' => 5,
        'per_page' => 10,
    ];

    /**
     * this function is for converting the return value of a resource as an array
     *
     * @param  mixed  $data the data that  has to be converted
     * @param  bool  $multiple if you want to return an array of data
     */
    public function convertResourceToArray(mixed $data, bool $multiple = false): array
    {
        if (! $multiple) {
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

    public function requestPathHook($data = ''): void
    {
        $this->requestPath = $data;
    }

    public function setUp(): void
    {
        if(isset($this->userType)){
            Artisan::call('db:seed RoleSeeder');
        }

        $this->signIn($this->userType);
    }

    public function signIn($type = null): void
    {
        $this->user = User::factory()->create() ;

        if(isset($null)){
            $this->user->assignRole($this->userType) ;
        }

        $this->be($this->user) ;
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

    public function failedMultiResponse()
    {
        $this->responseBody['data'] = [];
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    public function failedFalseResponse()
    {
        $this->responseBody['data'] = false;
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    public function checkSoftDeleteColumn()
    {
        $tableName = (new $this->model)->getTable();
        $columns = Schema::getColumnListing($tableName);

        if (in_array('deleted_at', $columns)) {
            return true;
        }

        return false;
    }
}
