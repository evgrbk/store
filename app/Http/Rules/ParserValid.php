<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class ParserValid implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $filesList = glob(base_path() . '/app/Services/GoodsImport/Vendors/*.php');
        foreach ($filesList as $file) {
            if (basename($file, ".php") == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute не найден';
    }
}
