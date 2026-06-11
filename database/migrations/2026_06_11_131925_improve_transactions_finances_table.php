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
        Schema::table('transactions_finances', function (Blueprint $table) {
            // Make Idcource nullable
            $table->foreignId('Idcource')->nullable()->change();

            if (!Schema::hasColumn('transactions_finances', 'devise')) {
                $table->string('devise', 5)->default('CDF')->after('montant');
            }
            if (!Schema::hasColumn('transactions_finances', 'Idagence')) {
                $table->foreignId('Idagence')->nullable()->after('Idcource')->constrained('agences', 'Idagence')->onDelete('cascade');
            }
            if (!Schema::hasColumn('transactions_finances', 'Idchauffeur')) {
                $table->foreignId('Idchauffeur')->nullable()->after('Idagence')->constrained('chauffeurs', 'Idchauffeur')->onDelete('set null');
            }
            // Idsuccursale is likely already there from previous migrations
            if (!Schema::hasColumn('transactions_finances', 'Idsuccursale')) {
                $table->foreignId('Idsuccursale')->nullable()->after('Idchauffeur')->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            }
            if (!Schema::hasColumn('transactions_finances', 'reference_paiement')) {
                $table->string('reference_paiement')->nullable()->after('mode_paiement_Enum');
            }
            if (!Schema::hasColumn('transactions_finances', 'description')) {
                $table->text('description')->nullable()->after('Type_Transaction_Enum');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions_finances', function (Blueprint $table) {
            $table->foreignId('Idcource')->nullable(false)->change();
            // Drop columns if they exist
            $columns = ['devise', 'Idagence', 'Idchauffeur', 'Idsuccursale', 'reference_paiement', 'description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('transactions_finances', $column)) {
                    // Note: SQLite might have trouble dropping foreign keys/columns in some versions
                    // but we'll try standard Laravel way.
                    try {
                        $table->dropColumn($column);
                    } catch (\Exception $e) {}
                }
            }
        });
    }
};
