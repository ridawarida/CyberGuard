# Module 1 (Johra) - Quick Escape "Panic Button" Backend

Feature owner: Johra-E-Jannat Oishy
Branch: `johra-panic-button`
Scope of this delivery: **backend only**. No Blade views, no CSS, no panic script yet.

---

## 1. Why the panic button needs a backend at all

The visible button is JavaScript, but JavaScript alone leaves the victim exposed:

| Risk if it were client side only | What the backend does about it |
| --- | --- |
| Abuser presses Back and the half typed report is restored from cache | `PreventSensitiveCaching` middleware plus `no-store` headers force a fresh request |
| Session cookie survives, so reopening the tab resumes the wizard | `PanicController::wipeSession()` flushes and invalidates the server session |
| Decoy site is hardcoded, so changing it needs a code deploy | `panic_settings` table, editable by an admin over the API |
| Nobody knows whether the feature is even used | `panic_events` anonymous counter feeding Module 3 metrics |
| Victim has JavaScript disabled | `POST /panic/escape` does the wipe and the redirect server side |

---

## 2. Database

### `panic_settings` (one active row)

| Column | Type | Default | Purpose |
| --- | --- | --- | --- |
| `decoy_url` | string | `https://www.wikipedia.org` | Where the tab is sent |
| `decoy_label` | string | `Wikipedia` | Shown in the admin panel |
| `hotkey_enabled` | bool | true | Turn the Escape key trigger on or off |
| `hotkey_press_count` | tinyint | 2 | How many Escape presses |
| `hotkey_window_ms` | smallint | 800 | Time window for those presses |
| `clear_form_fields` | bool | true | Client wipes inputs before leaving |
| `clear_local_storage` | bool | true | Client wipes localStorage |
| `replace_history_entry` | bool | true | Use `location.replace` so Back does not return |
| `log_events` | bool | true | Anonymous counter on or off |
| `is_active` | bool | true | Only the active row is served |

### `panic_events` (anonymous counter)

`trigger_source` (`click` / `hotkey` / `fallback`), `context` (`public` / `wizard` / `timeline` / `dashboard` / `unknown`), timestamps.

**Deliberately absent:** no IP, no user id, no session id, no user agent, no URL. If the database is ever leaked, this table cannot identify a single victim. Mention this in the viva, it is the strongest design decision in the feature.

---

## 3. API contract for the frontend

### `GET /panic/config`
Public. Response:

```json
{
  "status": "success",
  "data": {
    "decoy_url": "https://www.wikipedia.org",
    "decoy_label": "Wikipedia",
    "hotkey_enabled": true,
    "hotkey_press_count": 2,
    "hotkey_window_ms": 800,
    "clear_form_fields": true,
    "clear_local_storage": true,
    "replace_history_entry": true
  }
}
```

### `POST /panic/trigger`
Public, throttled 60/min. Needs the CSRF token header. Body:

```json
{ "source": "click|hotkey", "context": "public|wizard|timeline|dashboard|unknown" }
```

Returns `data.redirect_url` and `data.session_cleared`, forgets the session and XSRF cookies, and sends `Clear-Site-Data: "cache", "cookies", "storage"`.

**Important for whoever writes the JS:** fire this with `keepalive: true` and redirect immediately without awaiting the response. The escape must never wait on the network.

```js
fetch('/panic/trigger', {
  method: 'POST',
  keepalive: true,
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({ source: 'hotkey', context: 'wizard' })
});
window.location.replace(decoyUrl);
```

The layout needs `<meta name="csrf-token" content="{{ csrf_token() }}">` in `<head>` for this to work.

### `POST /panic/escape`
No JavaScript fallback. A plain Blade form with `@csrf` posts here, server wipes the session and 302s to the decoy site.

### Admin (needs `auth:sanctum` + `role:admin`)
- `GET /panic/admin/settings`
- `PUT /panic/admin/settings`
- `GET /panic/admin/stats?months=6`

`decoy_url` validation: must be a valid `https://` URL and must not point back at CyberGuard.

---

## 4. Files added

```
app/Http/Controllers/PanicController.php
app/Http/Controllers/Admin/PanicSettingController.php
app/Http/Middleware/PreventSensitiveCaching.php
app/Models/PanicSetting.php
app/Models/PanicEvent.php
database/migrations/2026_08_15_090000_create_panic_settings_table.php
database/migrations/2026_08_15_090100_create_panic_events_table.php
database/seeders/PanicSettingSeeder.php
routes/panic.php
tests/Feature/PanicButtonTest.php
docs/module1-johra-panic-button.md
```

Shared files touched, two lines total, to keep git merges painless:
- `routes/web.php`: one `require __DIR__ . '/panic.php';` line at the bottom
- `database/seeders/DatabaseSeeder.php`: one `$this->call(PanicSettingSeeder::class);` line

`bootstrap/app.php` was **not** touched. `PreventSensitiveCaching` is applied by class name in routes, so no alias registration is needed and no merge conflict with the `role` alias.

---

## 5. Running it locally

```bash
composer install
cp .env.example .env
php artisan key:generate

# local MySQL, not Railway
php artisan migrate
php artisan db:seed --class=PanicSettingSeeder

php artisan serve
```

Verify:

```bash
curl http://127.0.0.1:8000/panic/config
php artisan route:list --path=panic
php artisan test --filter=Panic
```

---

## 6. Applying the cache guard to victim pages

Once the group agrees, add this to the wizard routes so the Back button cannot resurrect a report:

```php
Route::prefix('incident/wizard')
    ->middleware(\App\Http\Middleware\PreventSensitiveCaching::class)
    ->group(function () { ... });
```

Do this in a separate commit and tell Ishrat and Nahin first, since it edits their route blocks.

---

## 7. Still to build (frontend, not in this delivery)

- Floating red button partial in `resources/views/layouts/app.blade.php`
- `public/js/panic.js` implementing click plus double Escape, reading `/panic/config`
- Admin Blade screen for the settings form
