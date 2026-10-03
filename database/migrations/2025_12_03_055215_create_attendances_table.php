<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // adjust if users table name differs
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->string('clock_in_photo')->nullable();   // storage path
            $table->string('clock_out_photo')->nullable();
            $table->string('clock_in_ip', 45)->nullable();  // IPv6 safe
            $table->string('clock_out_ip', 45)->nullable();
            $table->string('clock_in_user_agent')->nullable();
            $table->string('clock_out_user_agent')->nullable();
            // optional: location columns if you want geolocation via browser
            $table->decimal('clock_in_lat', 10, 7)->nullable();
            $table->decimal('clock_in_lng', 10, 7)->nullable();
            $table->decimal('clock_out_lat', 10, 7)->nullable();
            $table->decimal('clock_out_lng', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
