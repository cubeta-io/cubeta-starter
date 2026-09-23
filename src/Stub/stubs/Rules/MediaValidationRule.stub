<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Http\UploadedFile;
use App\Serializers\SerializedMedia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

readonly class MediaValidationRule implements ValidationRule
{
    /**
     * @param string[] $fileRules
     */
    public function __construct(
        private array $fileRules = ['image:allow_svg', 'max:10000', 'mimes:jpeg,png,jpg,gif,svg,webp'],
    ) {}

    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     *
     * @throws Exception
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof UploadedFile) {
            $fileToValidate = $value;
        }
        elseif ($value instanceof SerializedMedia) {
            if (! $value->exists()) {
                $fail('Invalid media file');

                return;
            }
            $fileToValidate = $value->getUploadedFile();
        }
        else {
            if (! SerializedMedia::isMediaArray($value)) {
                $fail('Invalid media file');

                return;
            }

            $serializedMedia = new SerializedMedia($value);

            if (! $serializedMedia->exists()) {
                $fail('Invalid media file');

                return;
            }

            $fileToValidate = $serializedMedia->getUploadedFile();
        }

        $validator = Validator::make(
            ['file' => $fileToValidate],
            ['file' => $this->fileRules],
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first());
        }
    }
}