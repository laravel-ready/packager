@php
    echo '<?php'
@endphp

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::{{ $SCHEMA_TYPE }}('{{ $TABLE_NAME }}', function (Blueprint $table) {
            @if ($SETUP_SCHEMA_CREATE)$table->id();
            $table->timestamps();@endif
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        @if ($SETUP_SCHEMA_CREATE)
        Schema::dropIfExists('{{ TABLE_NAME }}');
        @endif
    }
};
