## CHANGELOG

lists items that might need to run manually.

* 20250313

Ensure these pacakges are in `package.json`

```json
...
    "floating-vue": "^2.0.0-beta.1",
    "vue-markdown-render": "^2.1.1",
    "@dcodegroup/vue-mention": "^0.0.1",
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