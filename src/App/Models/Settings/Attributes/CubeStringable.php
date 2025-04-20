<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Illuminate\Support\Str;

class CubeStringable extends CubeAttribute implements HasFakeMethod
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString($this->name, "fake()->word()");
    }

    protected function guessStringMethod(): string
    {
        $name = $this->name;
        if (Str::contains($name, 'phone')) {
            $name = 'phone';
        } elseif (Str::contains($name, ['latitude ', '_lat', 'lat_']) || $name == 'lat' || $name == 'latitude') {
            $name = 'lat';
        } elseif (Str::contains($name, ['longitude', '_lon', '_lng', 'lon_', 'lng_']) || $name == 'lng' || $name == 'lon' || $name == 'longitude') {
            $name = 'lng';
        } elseif (Str::contains($name, 'address')) {
            $name = 'address';
        } elseif (Str::contains($name, 'street')) {
            $name = 'street';
        } elseif (Str::contains($name, 'city')) {
            $name = 'city';
        } elseif (Str::contains($name, 'country')) {
            $name = 'country';
        } elseif (Str::contains($name, ['zip', 'post_code', 'postcode', 'PostCode', 'postCode', 'ZIP'])) {
            $name = 'postcode';
        } elseif (Str::contains($name, 'gender')) {
            $name = 'gender';
        }

        return match ($name) {
            'name', 'username', 'first_name', 'last_name', 'user_name' => "firstName",
            'email' => "email",
            'phone' => "phoneNumber",
            'lat' => "latitude",
            'lng' => "longitude",
            'address' => "address",
            'street' => "streetName",
            'city' => "city",
            'country' => "country",
            'postcode' => "postcode",
            'gender' => "randomElement(['male' , 'female'])",
            default => "word"
        };
    }
}