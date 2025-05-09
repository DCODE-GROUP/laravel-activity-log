## CHANGELOG

lists items that might need to run manually.
* 20250509

Added – Support for `extra_models` parameter in the Activity Log API endpoint.
`extra_models`: A comma-separated list of relationship method names to include related models' activities.

When provided, the system will dynamically load specified relationships, and include related models' activities in the query using `orWhere` clauses.
This enhancement allows for broader and more flexible activity retrieval across related models.



* 20250502

Add index to the `type` column in the `activity_logs` table.

Migration file below

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('type');
        });
    }
};
```

```php

* 20250320

The package provides the following events:

* **`ActivityLogCommentCreated`**: This event is fired when an activity log comment is created. The event receives the
  activity log
  instance.
* **`ActivityLogCommentDeleted`**: This event is fired when an activity log comment is deleted. The event receives the
  activity log instance.
* **`ActivityLogCommentUpdated`**:  This event is fired when an activity log comment is update. The event receives the
  activity log instance.
* **`ActivityLogCommunicationRead`**:  This event is fired when an communication log is marked read. The event receives
  the
  activity log instance.


* 20250317

Ensure these pacakges are in `package.json`

```json
...
"floating-vue": "^5.2.2",
"vue-markdown-render": "^2.1.1",
"@dcodegroup/vue-mention": "^0.0.2",
```

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
