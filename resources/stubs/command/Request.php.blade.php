@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Http\Requests{{ $APPEND_NAMESPACE }};

use Illuminate\Foundation\Http\FormRequest;

class {{ $MAKE_CLASSNAME }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
