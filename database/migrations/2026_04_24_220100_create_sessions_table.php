<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            $this->assertSessionsTableStructure();
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    private function assertSessionsTableStructure(): void
    {
        $requiredColumns = ['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity'];

        if (! Schema::hasColumns('sessions', $requiredColumns)) {
            throw new RuntimeException('The sessions table exists but is missing one or more required columns.');
        }

        $database = DB::getDatabaseName();
        $columnRows = DB::select(
            'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, CHARACTER_MAXIMUM_LENGTH
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'sessions']
        );

        $columns = [];
        foreach ($columnRows as $row) {
            $columns[$row->COLUMN_NAME] = $row;
        }

        $this->assertColumnDefinition($columns, 'id', 'varchar', 'NO', 255, true);
        $this->assertColumnDefinition($columns, 'user_id', 'bigint', 'YES');
        $this->assertColumnDefinition($columns, 'ip_address', 'varchar', 'YES', 45);
        $this->assertColumnDefinition($columns, 'user_agent', 'text', 'YES');
        $this->assertColumnDefinition($columns, 'payload', 'longtext', 'NO');
        $this->assertColumnDefinition($columns, 'last_activity', 'int', 'NO');
    }

    /**
     * @param  array<string, object>  $columns
     */
    private function assertColumnDefinition(
        array $columns,
        string $name,
        string $type,
        string $isNullable,
        ?int $maxLength = null,
        bool $isPrimary = false
    ): void {
        if (! isset($columns[$name])) {
            throw new RuntimeException("The sessions table is missing the '{$name}' column.");
        }

        $column = $columns[$name];

        if (strtolower((string) $column->DATA_TYPE) !== $type) {
            throw new RuntimeException("The '{$name}' column has an invalid type in sessions table.");
        }

        if ((string) $column->IS_NULLABLE !== $isNullable) {
            throw new RuntimeException("The '{$name}' column has an invalid nullability in sessions table.");
        }

        if ($maxLength !== null && (int) $column->CHARACTER_MAXIMUM_LENGTH !== $maxLength) {
            throw new RuntimeException("The '{$name}' column has an invalid length in sessions table.");
        }

        if ($isPrimary && (string) $column->COLUMN_KEY !== 'PRI') {
            throw new RuntimeException("The '{$name}' column must be the primary key in sessions table.");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
