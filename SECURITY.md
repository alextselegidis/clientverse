# Security Policy

## Supported versions

Clientverse is a self-hosted application, so security fixes only ever land in a new release.
Only the latest release line receives them — there are no backports to older ones.

| Version | Supported |
|---------|-----------|
| 1.0.x   | Yes       |
| < 1.0   | No        |

## Reporting a vulnerability

**Please do not open a public issue for a security problem.**

Report it privately instead, in either of these ways:

* Open a private report through GitHub: **Security → Report a vulnerability** on
  [the repository](https://github.com/alextselegidis/clientverse/security/advisories/new).
* Or use the contact form at [alextselegidis.com](https://alextselegidis.com) if you would rather
  not use GitHub.

Please include:

* The Clientverse version, PHP version and database you are running
* What an attacker can do, and the access level they need to start (unauthenticated, any signed-in
  user, admin)
* The steps or request sequence that reproduces it
* Any log output or stack trace, with credentials and customer data removed

## What to expect

* An acknowledgement within a few days.
* An assessment of whether the report is reproducible and in scope, with the reasoning either way.
* A fix released as soon as it is ready, and a GitHub security advisory published alongside it.
* Credit in the advisory and in `CHANGELOG.md`, unless you would rather stay anonymous.

Please give us a reasonable window to ship a fix before disclosing the problem publicly.

## Out of scope

These are known properties of a self-hosted application rather than vulnerabilities:

* Findings that need a server the reporter already controls, or filesystem or database access
* A default installation left with the shipped `admin@example.org` / `12345678` credentials, an
  unrotated `APP_KEY`, `APP_DEBUG=true` in production, or a world-writable `storage` directory —
  the installation steps in the README cover all of these
* Missing HTTP security headers, TLS configuration and rate limiting, which belong to the web
  server or reverse proxy in front of the application
* Vulnerabilities in a dependency with no demonstrated impact on Clientverse itself; report those
  upstream
* Automated scanner output submitted without a working reproduction
