<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('reviewed_intent')->nullable()->after('priority_confidence');
            $table->string('reviewed_priority')->nullable()->after('reviewed_intent');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_priority');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'reviewed_intent',
                'reviewed_priority',
                'reviewed_at',
            ]);
        });
    }
};
