@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Models{{ $APPEND_NAMESPACE }};

use Illuminate\Database\Eloquent\Model;

class {{ $MAKE_CLASSNAME }} extends Model
{
    /**
     * Table name
     */
    protected $table = 'test';

    /**
     * Fillable fields
     */
    protected $fillable = [];

    /**
     * Type casts
     */
    protected $casts = [];

    /**
     * Date fields
     */
    protected $dates = [];

    /**
     * Append attributes
     */
    protected $appends = [];
}
