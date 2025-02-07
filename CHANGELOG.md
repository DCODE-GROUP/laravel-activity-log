## CHANGELOG

lists items that might need to run manually.

* 20250207 - 2.0.14
  Create a migration to add the `session_uuid` to the `activity_logss` table.

```php
 public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->uuid('session_uuid')->nullable()->after('diff');
        });
    }
```

* 20240618
  Added the exception block to the language file.

```php
    'exceptions' => [
        'model_key' => 'No model key has been found for :model',
    ],

````

*20240221
Might need a local migration to add title to the local app.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('title')->nullable()->default('make a change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
```