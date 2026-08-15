# Moderator Incident Assessment and Case Lifecycle Updates

**Member:** Anika Akter (23101509)
**Module:** 2
**Branch suggestion:** `anika-moderator-review`

## What this feature does

An authenticated workspace where moderators manage submitted cyberbullying reports:

1. Filter incidents by submission date range, platform, region, status, and tracking code.
2. Claim an incident, which locks it to that moderator's account and removes it from the open pool so two people never process the same case.
3. Read the full narrative and inspect the attached evidence screenshot, but only for a case they own.
4. Record internal evaluation notes, set a severity level, and move the case through its lifecycle.

Lifecycle states: `New` to `Investigating` to `Escalated` to `Resolved` or `Dismissed`.
Severity levels: `Unassigned`, `Low`, `Medium`, `High`, `Critical`.

## Files added

| Path | Purpose |
| --- | --- |
| `database/migrations/2026_08_15_120000_add_moderation_fields_to_incidents_table.php` | Adds `assigned_moderator_id`, `claimed_at`, `moderator_notes`, `reviewed_at` |
| `app/Http/Controllers/Moderator/IncidentReviewController.php` | Index, show, claim, release, update |
| `app/Http/Controllers/StaffAuthController.php` | Session login for browser pages |
| `app/Http/Middleware/StaffAccess.php` | Redirects guests, blocks wrong roles |
| `resources/views/auth/login.blade.php` | Staff sign in page |
| `resources/views/moderator/incidents/index.blade.php` | Filterable case table with Claim buttons |
| `resources/views/moderator/incidents/show.blade.php` | Case file plus assessment form |
| `tests/Feature/ModeratorIncidentReviewTest.php` | 11 Pest feature tests |

## Files modified

| Path | Change |
| --- | --- |
| `app/Models/Incident.php` | New fillable fields, casts, `assignedModerator()` relation, claim helpers, `filter`/`unclaimed`/`claimedBy` scopes |
| `app/Models/User.php` | `assignedIncidents()` relation |
| `app/Providers/AppServiceProvider.php` | `Paginator::useBootstrapFive()` so pagination matches Bootstrap 5 |
| `bootstrap/app.php` | Registers the `staff` middleware alias |
| `routes/web.php` | Staff login routes and the `moderator/incidents` route group |

Nothing in Ishrat's wizard, Nahin's timeline, or the existing API routes was touched.

## Why a new login controller was needed

The existing `AuthController` is API only. It returns JSON and issues Sanctum bearer tokens, which a browser page cannot attach to a normal link click. `StaffAuthController` uses `Auth::attempt()` and the session guard instead. Both can coexist: the API keeps using `auth:sanctum` and `role`, the web pages use `staff`.

`RoleMiddleware` was left untouched for the same reason. It answers with a JSON 401, which is correct for the API but would show a raw JSON blob to a moderator in a browser.

## How to run it

```bash
php artisan migrate
php artisan db:seed          # creates moderator@cyberguard.com / mod123
php artisan storage:link     # so evidence screenshots resolve
php artisan serve
```

Then open `http://localhost:8000/staff/login`.

Seeded accounts:

- Moderator: `moderator@cyberguard.com` / `mod123`
- Admin: `admin@cyberguard.com` / `admin123`

## Routes

| Method | URI | Name |
| --- | --- | --- |
| GET | `/staff/login` | `staff.login` |
| POST | `/staff/login` | `staff.login.submit` |
| POST | `/staff/logout` | `staff.logout` |
| GET | `/moderator/incidents` | `moderator.incidents.index` |
| GET | `/moderator/incidents/{incident}` | `moderator.incidents.show` |
| POST | `/moderator/incidents/{incident}/claim` | `moderator.incidents.claim` |
| POST | `/moderator/incidents/{incident}/release` | `moderator.incidents.release` |
| PUT | `/moderator/incidents/{incident}` | `moderator.incidents.update` |

The list view has three tabs driven by `?scope=`: `pool` (unclaimed), `mine` (yours), `all`.

## Tests

```bash
php artisan test --filter=ModeratorIncidentReview
```

Covered: guest redirect, pool hides claimed cases, platform filter, claim locks the row, second claim is refused, another moderator cannot open your case, unclaimed case cannot be opened directly, assessment saves, invalid status rejected, release returns to pool, admin override.

## Design decision worth defending in viva

Claiming runs inside a database transaction with `lockForUpdate()`. Without the row lock, two moderators clicking Claim at the same moment could both read `assigned_moderator_id` as null and both write their own id, producing exactly the duplicate processing the requirement asks to prevent. The lock makes the second request read the already updated row and fail cleanly.

## Notes for teammates

- The migration deliberately skips a foreign key constraint on `assigned_moderator_id` because SQLite, used by the test suite, cannot add one to an existing table. An index is added instead.
- Anika's other features (Canvas redaction tool, Perspective API scanner, external policy link CRUD) are not part of this branch.
