# Release Notes

## [Unreleased]

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
