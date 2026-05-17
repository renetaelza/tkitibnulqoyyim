<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id('id_registration');
            $table->foreignId('id_user')->constrained('users', 'id')->onDelete('cascade');
            $table->json('candidate_data'); // Berisi: name, birth_place, birth_date, gender
            $table->json('parents_data');   // Berisi: father_name, mother_name, father_phone_num, mother_phone_num, father_occupation, mother_occupation, father_address, mother_address
            $table->string('group')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
