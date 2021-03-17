<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class FieldsValid implements Rule
{
    /**
     * Good name with error
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Fields
     *
     * @var array
     */
    protected $fields;


    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $dests = array_keys(Arr::collapse($this->fields['dest']));

        foreach ($value as $arrValue) {
            [$src, $dest] = Arr::flatten(Arr::divide($arrValue));
            if (!in_array($src, $this->fields['src'])) {
                $this->fieldName = $src;
                return false;
            }
            if (!in_array($dest, $dests)) {
                $this->fieldName = $dest;
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Поле ' . $this->fieldName . ' некорректно.';
    }
}
