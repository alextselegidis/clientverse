# Clientverse

Self-hosted CRM (customers, contacts, projects, milestones, sales, contracts) built as a
plain Laravel + Blade application. `SPECS.md` holds the module specifications.

## Non-negotiable constraints

These are deliberate product decisions, not gaps to fill:

- **Blade only.** No Livewire, Inertia, Vue, React or any SPA layer.
- **No JavaScript framework.** `public/scripts/clientverse.js` is a handful of plain-DOM
  helpers loaded with a `<script>` tag. Bootstrap's own bundle is the only JS dependency.
  Vite exists but is effectively unused — do not move assets into it without being asked.
- **No new dependencies** unless there is no reasonable way to do it with what is here.
- **Keep the current app structure.** Controllers stay thin, models hold the query scopes,
  shared markup lives in `resources/views/shared/`.
- **Never create a branch** unless the developer explicitly asks. Work on the current one.
- **Never commit or push** unless the developer explicitly asks.
- **Commit messages are a single line.** Subject only - no body, and no trailing
  attribution lines: no `Co-Authored-By:` trailer and no "Generated with Claude Code"
  line, in commit messages or in pull request descriptions.

## Stack

- PHP 8.2+, Laravel 12, MySQL 8
- Laravel Sanctum for API tokens, `tailflow/laravel-orion` for the REST API resources
- Bootstrap 5.3 (Bootswatch **Flatly** build), Bootstrap Icons, pace-js — all vendored
  under `public/vendor/`, none installed at runtime
- Assets are served straight from `public/`, cache-busted with `?{{config('app.version')}}`

## Local environment

Everything runs in Docker; host ports are randomised by Compose, so look them up:

```bash
docker compose up -d
docker compose ps                     # find the nginx host port
docker compose exec php-fpm bash      # composer / artisan live in here
```

| Service    | Purpose                    |
|------------|----------------------------|
| nginx      | the app                    |
| php-fpm    | PHP, artisan, composer     |
| mysql      | database `clientverse`     |
| phpmyadmin | database UI                |
| mailpit    | catches outgoing mail      |
| swagger-ui | serves `openapi.yml`       |

Common commands (run inside the `php-fpm` container):

```bash
php artisan migrate
php artisan db:seed --class=DemoSeeder   # ~100 customers with full history
php artisan test
php artisan view:clear                   # after editing Blade templates
vendor/bin/pint                          # PHP formatting
```

Tests use an in-memory SQLite connection (set in `phpunit.xml`). Keep it that way —
without it `RefreshDatabase` wipes the developer's MySQL database.

## Layout of the code

```
app/Auth/AppSessionGuard.php       session guard: per-install recaller cookie, drops
                                   deactivated users on every request
app/Http/Controllers/              one controller per resource, plus Api/V1/* for the API
app/Http/Middleware/               ExtendRememberSession keeps "remember me" sessions alive
helpers.php                        sort_link(), setting(), default_currency(), format_currency()
resources/views/layouts/           main / auth / message layouts, all sharing shared/head
resources/views/pages/             one file per screen: <resource>, -show, -edit
resources/views/shared/            partials: head, header, footer, breadcrumb, pagination,
                                   show-* and *-value field renderers, bulk actions
public/styles/clientverse.css      every style override; there is no build step
public/scripts/clientverse.js      toasts, table dropdowns, unsaved-changes guard
public/manifest.json, public/sw.js PWA manifest and service worker
```

## Conventions

- Every PHP and Blade file starts with the project header comment block. Copy it from a
  neighbouring file when adding one.
- All user-facing strings go through `__('key')` with the key added to `lang/en.json`.
- List pages follow one shape: `card card-table` → `table-filters` → `table-responsive`
  → `shared.pagination`. Copy an existing list page rather than inventing a new layout.
- Detail and edit pages use the `shared/show-*` and `shared/*-value` partials for fields.
- Formatting: 4 spaces, LF, 120 columns, single quotes (see `.editorconfig`,
  `.prettierrc.json`). `vendor/bin/pint` for PHP.

## UI conventions

- Design tokens live in the `:root` block of `public/styles/clientverse.css` — the palette,
  radii, shadows, the neutral ramp (`--cv-canvas`, `--cv-surface-muted`, `--cv-hairline`)
  and the font stack. Change a token there rather than hardcoding a colour in a template.
- Palette: `--bs-primary` is `#33507a` (deep slate blue) and the semantic colours are
  deliberately desaturated versions of the Bootswatch defaults — this is a tool for
  professionals, so no consumer-app teal or orange. Every semantic colour also has its
  `-rgb`, `-bg-subtle`, `-border-subtle` and `-text-emphasis` token set; keep the four in
  step when changing one. The brand colour is also duplicated in
  `shared/head.blade.php`, `public/manifest.json` and `public/offline.html`.
- Font is the system UI stack. Do not add a webfont — self-hosted installs should not
  reach out to a CDN.
- Buttons: `btn-primary` for the committing action, `btn-light border` for neutral ones.
  There is no `btn-dark` any more.
- Row actions are an icon-only `⋯` (`bi-three-dots`) button with an `aria-label`, never a
  labelled dropdown.
- Badges render as quiet tinted pills; write `badge bg-success` and the CSS maps it to the
  `*-bg-subtle` / `*-text-emphasis` pair. Do not hand-roll `bg-*-subtle text-*` combos.
- Tables are hover-only (no `table-striped`) with `table-light-header` for the quiet
  uppercase header row.
- **Row density.** A row's height is set by whichever cell wraps, so keep them from
  wrapping rather than shrinking the type: dates and money get `text-nowrap`, record
  names are clamped (`.table td .fw-medium`, two lines with a pointer and three on a
  phone), and cell padding is `0.5rem` vertically, going back to `0.75rem` below `lg`
  where a row is a tap target. A column that cannot fit on one line at `lg` belongs at
  `xl` - that is where the contracts date range lives. Desktop rows land at ~48px.
- Global search is the navbar icon opening `#search-modal` (in `shared/header.blade.php`) —
  a centred, header-free dialog holding just the input. The navbar is too tight for a
  usable inline input.
- `SearchController` counts each section's full result set and lists only the first
  `PER_SECTION`; `shared/search-section` renders the heading, the real total, and a
  "view all" link into that resource's own `?q=` filtered list. Never report the number of
  rows fetched as the number of matches.
- `images/logo-light.svg` is the white mark for the dark navbar; `images/logo.png` is the
  original for light backgrounds.

## Several installs on one domain

Browsers key cookies by name, path and domain only, so two Clientverse installs on
the same host would overwrite each other's cookies and signing in to one would sign
you out of the other. Every cookie name the app controls therefore ends in
`cookie_suffix()` (`helpers.php`) — a short hash of this install's `APP_KEY` and
`APP_URL`:

- the session cookie, via `config/session.php`
- the "remember me" recaller, via `AppSessionGuard::getRecallerName()`
- the CSRF cookie, via `App\Http\Middleware\ValidateCsrfToken`, swapped into the web
  group in `bootstrap/app.php` with `replaceInGroup` (plain `replace` only touches the
  global stack, not groups)

The suffix includes `APP_KEY` and not only `APP_URL` because `APP_URL` is often left at
its default or shared between two installs under different paths. If you add a cookie,
suffix its name too.

## Mobile

Every screen is checked at 390px and 768px. Two rules carry most of it:

- **Column priority, in three tiers.** A list table shows the record name (`w-100
  w-md-auto`) plus its status column on a phone, so it fits with no sideways scrolling
  and the name gets the freed space. Exactly one secondary column is marked `d-none
  d-md-table-cell` and appears on a tablet - the most identifying or actionable one
  (email, customer, value, due date) - because a 768px table showing two columns wastes
  half its width. Everything else is `d-none d-lg-table-cell`. Add a column and you
  decide which of the three tiers it belongs on. `.table-responsive` is still there as
  the safety net.
- **Touch targets.** One media query near the end of `public/styles/clientverse.css`
  brings sort links, `btn-sm`, breadcrumb links, dropdown items, page links and
  `mailto:`/`tel:` links up to roughly 40px below `lg`; checkboxes go to 24px with a
  label of the same height. Adjust it there rather than per-page. Two rules in that
  block overlap, so the dense-link rule excludes `.dropdown-item` and `mailto:`/`tel:`
  explicitly - a selector like `.table td a` otherwise outweighs them and flattens a row
  action back to 30px.

Also below `lg`: bulk-select columns are hidden (touch multi-select is not worth the
width, and the toolbar never unhides without checkboxes), record names clamp to three
lines, a lone card-footer button goes full width, and the paginator wraps.

## Frontend gotchas

- The navbar expands at `lg`, but the even 120px nav-item grid only fits from `xl`, so
  `min-width` on `.nav-menu-item` is scoped to `min-width: 1200px` and the items use
  `px-lg-2 px-xl-4`. Without that the whole page scrolls sideways on a 1024px tablet in
  landscape. Adding a navbar item means re-checking 992px.
- `.table-responsive` scrolls horizontally below `992px` and is `overflow: visible` above,
  so row dropdowns can escape on desktop. Do not add inline `overflow` overrides — that
  breaks horizontal scrolling and pushes the whole page sideways on phones.
- `.flex-grow-1` is given `min-width: 0` globally so text truncates and scroll containers
  stay inside their column.
- `.bg-primary` is overridden but keeps honouring `.bg-opacity-*`, so `bg-primary
  bg-opacity-10` tiles are light — pair them with `text-primary`, never `text-white`.
- Focus rings are drawn on `.input-group:focus-within`, not on the individual control.
- Transitions are listed per interactive selector, not applied to `*` — do not reintroduce
  a blanket transition.
- The extra vertical padding applies to `input.form-control` only, so `rows` on a
  `<textarea>` still controls its height.
- The service worker never caches HTML (CSRF tokens would go stale); it caches the
  versioned static assets and falls back to `public/offline.html` for failed navigations.
