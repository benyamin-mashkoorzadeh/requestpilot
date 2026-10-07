<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('intent')->nullable();
            $table->float('intent_confidence')->nullable();
            $table->string('priority')->nullable();
            $table->float('priority_confidence')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'intent',
                'intent_confidence',
                'priority',
                'priority_confidence',
            ]);
        });
    }
};
