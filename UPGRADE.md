# Upgrade Guide

## Upgrading to 4.x from 3.x

Version **4.x** splits this package into a **PHP-only Composer package**. Vue/JS/CSS components that previously lived under `vendor/dcodegroup/activity-log` were moved to a separate frontend package (install with pnpm).

| Concern | Before (≤ 3.x) | After (4.x) |
| --- | --- | --- |
| Backend | `dcodegroup/activity-log` (Composer) | Same package, PHP API largely unchanged |
| Frontend | Vue/Sass loaded from the Composer vendor path | [`@dcodegroup-au/vue-activity-log`](https://www.npmjs.com/package/@dcodegroup-au/vue-activity-log) |
| Source | Bundled in this repo | [DCODE-GROUP/laravel-activity-log-vue](https://github.com/DCODE-GROUP/laravel-activity-log-vue) |

### 1. Update Composer

```bash
composer require dcodegroup/activity-log:^4.0
```

Requirements for 4.x:

- PHP `^8.3`
- Laravel `^10.10|^11.0|^12.0|^13.0` (from `4.0.1`; `4.0.0` required Laravel 11+)

Then:

```bash
php artisan migrate
composer dump-autoload
```

No new published PHP migrations are required for a plain 3.x → 4.x upgrade. Database schema for activity/communication logs is unchanged by the split itself.

### 2. Install the Vue package (if you still use the UI)

```bash
pnpm add @dcodegroup-au/vue-activity-log
```

Peer dependencies (install if missing):

```bash
pnpm add vue@^3 axios vue-i18n @heroicons/vue @dcodegroup/vue-mention mitt vue-markdown-render floating-vue
```

See the [Vue package README](https://www.npmjs.com/package/@dcodegroup-au/vue-activity-log) for full setup (plugin registration, mitt `bus`, props).

### 3. File-by-file changes in your app

#### `package.json`

**Remove** (optional cleanup — only needed previously to support vendor Vue/Sass):

```json
"floating-vue": "...",
"vue-markdown-render": "...",
"@dcodegroup/vue-mention": "...",
"@heroicons/vue": "..."
```

You may keep them if other parts of the app use them, or re-add as peers of `@dcodegroup-au/vue-activity-log`.

**Add**:

```json
"@dcodegroup-au/vue-activity-log": "^0.1.0"
```

#### `vite.config.js` (or `webpack.mix.js`)

**Remove** the vendor alias used to resolve Composer package assets:

```js
resolve: {
  alias: {
    "@dcodegroup": path.resolve(__dirname, "./vendor/dcodegroup/"),
  },
},
```

(Only remove this if nothing else in the app still imports from `@dcodegroup/...`.)

#### App JS entry (e.g. `resources/js/app.js` / `index.js`)

**Remove**:

```js
import VActivityLog from "@dcodegroup/activity-log/resources/js/components/VActivityLog.vue";
import ActivityLogList from "@dcodegroup/activity-log/resources/js/components/ActivityLogList.vue";
import ActivityEmail from "@dcodegroup/activity-log/resources/js/components/ActivityEmail.vue";

app.component("VActivityLog", VActivityLog);
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);
```

**Replace with** either the plugin:

```js
import VueActivityLogPlugin from "@dcodegroup-au/vue-activity-log";
import "@dcodegroup-au/vue-activity-log/dist/style.css";

app.use(VueActivityLogPlugin);
```

or named imports:

```js
import {
  VActivityLog,
  ActivityLogList,
  ActivityEmail,
} from "@dcodegroup-au/vue-activity-log";
import "@dcodegroup-au/vue-activity-log/dist/style.css";

app.component("VActivityLog", VActivityLog);
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);
```

Blade/Vue usage of `<VActivityLog>`, `<ActivityLogList>`, and `<ActivityEmail>` can stay the same if you keep those global component names.

#### Styles (e.g. `resources/sass/app.scss`)

**Remove**:

```scss
@import "@dcodegroup/activity-log/resources/sass/index.scss";
```

Styles now come from:

```js
import "@dcodegroup-au/vue-activity-log/dist/style.css";
```

You can keep `@import "floating-vue/dist/style.css";` if you still use Floating Vue elsewhere.

#### `tailwind.config.js`

**Remove** (if only added for this package):

```js
content: [
  "./vendor/dcodegroup/**/*.{blade.php,vue,js,ts}",
],
```

and any spacing tokens that existed solely for the old activity-log Sass (`3xlSpace`, `2xlSpace`, etc.), unless your app still uses them.

#### i18n / Vite i18n plugin

**Remove** loading translations from the Composer vendor path if present:

```js
additionalLangPaths: [
  "vendor/dcodegroup/activity-log/lang",
],
```

Laravel lang files are still published from the PHP package (`php artisan vendor:publish --tag=activity-log-translations`). Frontend copy is handled by the Vue package / your app i18n setup — follow the Vue package docs.

#### Published Sass / assets

4.x no longer publishes `activity-log-sass`. If you previously ran `activity-log:install` and have unused copies under `resources/sass/activity-log`, you can delete them.

### 4. Backend checklist (usually no code changes)

These remain valid in 4.x:

- Models using `ActivityLoggable` / `LogsActivity`
- `HasActivityUser` on the User model
- `EventServiceProvider` registration of `ActivityLogMessageSentListener` (if you track mail)
- Published `config/activity-log.php`
- Existing activity-log API routes (`php artisan route:list --name=activity-log`)

Optional: models that create logs may implement `Dcodegroup\ActivityLog\Contracts\LogsActivity` for clearer typing.

### 5. Rebuild frontend

```bash
pnpm install
pnpm run build
# or your project’s asset build command
```

---

## Upgrading from 2.x to 4.x

1. Upgrade to **3.x** first if you need a staged path (PHP `^8.3`, Laravel 11+ in 3.x), **or** jump straight to **4.0.1+** if you are on Laravel 10.10+ / 11 / 12 / 13 and PHP 8.3.
2. Complete the [3.x → 4.x](#upgrading-to-4x-from-3x) frontend split steps above.
3. Review [CHANGELOG.md](./CHANGELOG.md) for schema/API items added during 2.x/3.x (e.g. `title` column, `type` index, `extra_models`, mail listener, events).

---

## Upgrading from 1.x

- From **1.1.1+**, remove duplicate model observers; `bootActivityLoggable` on the `ActivityLoggable` trait handles model change listening.
- Then follow the 2.x → 4.x / 3.x → 4.x steps as appropriate for your Laravel/PHP version.
- See the compatibility table in [README.md](./README.md#compatibility).

---

## Activity log reactions (current mainline)

If your installed 4.x build includes emoji reactions on comments:

1. Run migrations after updating Composer (the `activity_log_reactions` migration is auto-loaded via `loadMigrationsFrom`):

   ```bash
   php artisan migrate
   ```

2. Ensure the Vue package is recent enough to consume `reactionGroups` / `reactionCounts` / `currentUserReaction` from the API (`@dcodegroup-au/vue-activity-log`).

Fresh installs still get the table from the published create-tables stub as well; the upgrade migration is safe if the table already exists.
