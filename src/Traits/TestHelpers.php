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

    protected $userType;

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
     * @return array
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
        parent::setUp();

        if (isset($this->userType)){
            Artisan::call('db:seed RoleSeeder');
        }

        $this->signIn($this->userType);
    }

    public function signIn($type = null): void
    {
        $this->user = User::factory()->create() ;
        if(isset($type)){
            $this->user->assignRole($type) ;
        }
        $this->be($this->user) ;
    }

    /**
     * this function for login using email address and default password is 12345678
     *
     * @param  string  $email
     * @param  string  $password
     * @return void
     */
    public function login(string $email, string $password = '12345678'): void
    {
        auth()->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * data = [] ||| message = there is no data
     * @return void
     */
    public function failedMultiResponse()
    {
        $this->responseBody['data'] = [];
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    /**
     * data = false ||| message  = there is no data
     * @return void
     */
    public function failedFalseResponse(): void
    {
        $this->responseBody['data'] = false;
        $this->responseBody['message'] = __('site.there_is_no_data');
    }

    /**
     * check if the model can softdelete
     * @return bool
     */
    public function checkSoftDeleteColumn(): bool
    {
        $tableName = (new $this->model)->getTable();
        $columns = Schema::getColumnListing($tableName);

        if (in_array('deleted_at', $columns)) {
            return true;
        }

        return false;
    }
}
