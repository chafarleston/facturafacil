<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('printer_name')->nullable();
            $table->string('printer_ip')->nullable();
            $table->integer('printer_port')->nullable();
            $table->enum('type', ['local', 'network'])->default('local');
            $table->string('job_type', 50)->default('kitchen')->comment('kitchen, prebill, invoice, cancel');
            $table->string('reference_type', 50)->nullable()->comment('App\Models\RestaurantOrder, App\Models\Invoice');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('data');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
