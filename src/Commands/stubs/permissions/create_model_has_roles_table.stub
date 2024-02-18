<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roleable_id');
            $table->string('roleable_type');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->index(['roleable_id', 'roleable_type', 'role_id']);
        });
    }
};
