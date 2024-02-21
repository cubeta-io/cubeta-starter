<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->index(['model_id', 'model_type', 'role_id'] , 'model_role_index');
        });
    }
};
