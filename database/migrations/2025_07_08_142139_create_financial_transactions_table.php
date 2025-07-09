<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->boolean('is_credit')->comment('true = income, false = expense');
            $table->date('booking_date');
            $table->string('transaction_number', 30)->nullable();
            $table->string('counterparty_details')->nullable();
            $table->string('title');
            $table->text('additional_info')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('person_id')->nullable()->constrained('people')->onDelete('set null');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_transactions');
    }
};
