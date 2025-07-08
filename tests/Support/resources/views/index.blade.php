@php
    $translations = realpath(app()->basePath('../../../../lang/en/activity-log.php'));
    $translations = ["activity-log" => require($translations)];
@endphp
<!DOCTYPE html>
<html>
<head>
  <title>Component Preview</title>
  <script>
  window.__translations = @json($translations);
</script>
  <script type="module" src="http://localhost:5173/develop.js"></script>
</head>
<body>
<div id="activity-log-app">

<div class="w-4/5 mx-auto mt-10">

  <section class="activity_log">
    <v-activity-log
    model-id="1"
    :is-widget-view="true"
    model-class="Workbench\App\Models\Ticket"
    :allow-comment="true"
    filter-event="activityTableFilterChange"
    >
    </v-activity-log>
  </section>
</div>
</div>
</body>
</html>