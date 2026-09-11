# Contributing to Clientverse

Thanks for taking the time to help out. This page covers how to report a problem, how to set up a
development environment, and what a pull request needs to be mergeable.

Everyone taking part is expected to follow the [Code of Conduct](CODE_OF_CONDUCT.md).

## Reporting bugs

Open an issue with the **Bug report** template. A report needs the Clientverse version, PHP
version, database and browser, the steps that reproduce the problem, and what you expected to
happen instead. Screenshots help for anything visual.

Please do **not** open a public issue for a security problem — follow the
[security policy](SECURITY.md) instead.

## Suggesting a feature

Open an issue with the **Feature request** template and describe the problem you are trying to
solve before the solution you have in mind. Clientverse deliberately stays small, so a feature
that only makes sense for one workflow is likely to be declined — that is not a judgement on the
idea, just on its fit.

## Development setup

Everything runs in Docker. Host ports are randomised by Compose, so look them up with
`docker compose ps`.

```bash
git clone https://github.com/alextselegidis/clientverse.git
cd clientverse
cp .env.example .env
# The example ships production defaults; for development set APP_ENV=local and APP_DEBUG=true.
docker compose up -d
docker compose exec php-fpm bash    # composer, artisan and pint live in here
```

Inside the `php-fpm` container:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoSeeder   # ~100 customers with full history
```

Then open the app on the host port that `docker compose ps` reports for nginx and sign in with
`admin@example.org` / `12345678`.

The container runs as `DOCKER_UID`/`DOCKER_GID` from `.env` (1000:1000 by default) so that
composer and artisan write files owned by you. Set them to your own `id -u` / `id -g` if those
differ.

## Before you open a pull request

Run these inside the `php-fpm` container:

```bash
php artisan test        # tests run against in-memory SQLite
vendor/bin/pint         # PHP formatting
```

Add a `CHANGELOG.md` entry under `## [Unreleased]` in the matching section (Added, Changed,
Fixed). Entries wrap at 120 columns.

## Project conventions

Clientverse is a plain Laravel + Blade application and stays that way. A pull request that
introduces any of the following will be declined:

* Livewire, Inertia, Vue, React or any other SPA layer
* A JavaScript framework or a build step for the front-end assets — `public/scripts/clientverse.js`
  is plain DOM helpers loaded with a `<script>` tag
* A new runtime dependency, where there is a reasonable way to do it with what is already here
* A webfont or any other asset fetched from a CDN — self-hosted installs should not reach out

Beyond that:

* Controllers stay thin; query scopes live on the models
* Shared markup lives in `resources/views/shared/`; list pages copy the shape of an existing list
  page rather than inventing a new layout
* Every user-facing string goes through `__('key')`, with the key added to `lang/en.json`
* Design tokens live in the `:root` block of `public/styles/clientverse.css` — change a token
  rather than hardcoding a colour in a template
* Every screen is checked at 390px and 768px
* Formatting is 4 spaces, LF, 120 columns, single quotes (see `.editorconfig` and
  `.prettierrc.json`)
* Every PHP and Blade file starts with the project header comment block — copy it from a
  neighbouring file

`CLAUDE.md` in the repository root documents these conventions in more detail, and `SPECS.md`
holds the module specifications.

## Pull request checklist

* One topic per pull request, branched off `main`
* A single-line commit subject, in the imperative
* Tests and `vendor/bin/pint` pass
* A `CHANGELOG.md` entry under `## [Unreleased]`
* Screenshots for any visual change, at desktop and phone width

## Licence

Clientverse is licensed under the [GPL v3.0](LICENSE). By contributing you agree that your work is
released under the same licence.
