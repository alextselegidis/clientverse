# Release Notes

## [Unreleased]

### Added

- Latest blog posts from clientverse.org on the About page, read once a day and cached

### Changed

- One secondary column in every list table now shows from 768px, so tablets no longer waste half the row width
- Denser desktop table rows: dates and money no longer wrap, record names clamp to two lines, tighter cell padding

### Fixed

- Incorrect `http://` URLs when deployed behind a reverse proxy; proxies are now trusted via the `TRUSTED_PROXIES` env var (#19)
- Row action dropdown items were 30px tall on a phone instead of the intended 40px
- `mailto:`/`tel:`, sort and breadcrumb links now reach the intended 40px touch target below 992px
- Page scrolled sideways between 992px and 1199px, so the navbar now fits a 1024px tablet in landscape
- List tables needed horizontal scrolling at 320px

## [1.0.0] - 2026-03-26

### Added

- Dashboard module with overview widgets, quick actions, recent activity feed, and performance metrics
- Customers module with company/individual types, status tracking (lead/active/inactive), tags, and metadata
- Contacts module nested under customers with roles (decision_maker/finance/technical/other), primary contact flag, and portal access
- Projects module with status workflow (planned/active/on_hold/completed), visibility settings, and team members
- Milestones module nested under projects with due dates and completion tracking
- Sales module with pipeline stages (lead/qualified/proposal_sent/won/lost), probability, and value tracking
- Contracts module with types (fixed/recurring), status tracking (draft/active/expired/cancelled), and linked projects/sales
- CRM navigation in header (Customers, Projects, Sales, Contracts)
- Breadcrumb navigation component for better UX
- Tags system with color support for customers
- Soft deletes for customers, projects, sales, and contracts

### Changed

- Transformed application from bookmark manager to CRM
- Updated routes for CRM module structure
- Updated language translations for CRM functionality

### Removed

- Links and Tags bookmark manager functionality
- Old bookmark-related controllers, models, views, and migrations
