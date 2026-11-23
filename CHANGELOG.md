# Release Notes

## [Unreleased]

### Added

- Latest blog posts from clientverse.org on the About page, read once a day and cached
- Invoices with line items, tax, payments and a derived status (unpaid, partially paid, paid, overdue)
- Estimates that convert into a draft invoice, and credit notes that reduce the balance of an invoice
- Items catalog that fills the price and tax of invoice and estimate lines
- Printable invoices, estimates and credit notes that save to PDF from the browser
- Signed customer links: view an invoice, accept or decline an estimate, sign a contract, answer a survey
- Emailing invoices, estimates and contracts to the customer from templates edited under Setup
- Expenses, optionally billed to a customer on a new draft invoice
- Pipeline board that moves deals between stages by dragging or with a stage menu
- Tasks with priorities, assignees and time tracking by timer or manual entry
- Support tickets with a reply thread and an optional email to the customer
- Per-user view, edit and delete permissions for each module, enforced on the web and API routes
- Reports for invoiced and collected amounts, outstanding balances, time logged and tickets, with a CSV export
- Goals that track invoicing, collections, new customers and won deals
- Client portal where contacts with portal access sign in to see their documents, projects and tickets, and open or
  reply to tickets
- Proposals with a subject, body and line items, created from a sale, accepted or declined through a signed link and
  converted into an estimate
- Recurring invoices that copy themselves weekly, monthly, quarterly or yearly and email each copy to the customer
- Optional reminder emails for overdue invoices, expiring estimates and proposals, and contracts about to end
- Online invoice payments through Stripe Checkout, recorded from Stripe's signed webhook
- Pages crossfade instead of flashing white, and a link starts loading while the pointer rests on it
- List search filters as you type, a form posts only once per click, and saving keeps the scroll position
- Month calendar of due dates across tasks, milestones, invoices, contracts and deals
- Subscriptions billed through Stripe: the customer subscribes on a signed page and every paid period is booked as a
  paid invoice; staff can cancel from the subscription page
- Online invoice payments through PayPal next to Stripe, captured when the customer returns from PayPal
- Custom fields (text, long text, number, date, dropdown, checkbox) on customers, contacts, projects, sales and
  contracts
- Support departments that tickets are filed under, picked by staff or by the contact in the portal
- Activity log under Setup of who created, changed or deleted which record, kept for a year
- Announcements for staff on the dashboard, for customers in the portal, or both, with an optional end date
- Knowledge base with a public page for published articles
- Surveys with choice, rating and text questions and a results page

### Changed

- One secondary column in every list table now shows from 768px, so tablets no longer waste half the row width
- Denser table rows: every row is one line tall, long record names and links cut off with an ellipsis instead of
  wrapping, tighter cell padding

### Fixed

- Customer dropdowns failed on SQLite because their labels were built with MySQL-only `IF()`
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
