<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace Database\Seeders;

use App\Enums\ProjectStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\File;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Sale;
use App\Models\Tag;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoSeeder
 * ---------------------------------------------------------------------------
 * Generates a realistic demo dataset for an IT services / managed support
 * company. Creates ~100 corporate customers along with the contacts, sales
 * pipeline, contracts, projects, milestones, notes, tags, files and internal
 * staff that you would expect to see in a working CRM.
 *
 * This seeder is intentionally NOT registered in DatabaseSeeder::run() so it
 * will never execute during a normal `php artisan db:seed`. Run it explicitly:
 *
 *     php artisan db:seed --class=DemoSeeder
 *
 * Re-running it is safe: it only appends new demo records and never touches
 * the default admin user that ships with the application.
 * ---------------------------------------------------------------------------
 */
class DemoSeeder extends Seeder
{
    /** Number of corporate customers to generate. */
    private const CUSTOMER_COUNT = 100;

    /** Number of internal staff users (account managers, engineers, etc.). */
    private const STAFF_COUNT = 12;

    /** Industry verticals — used to make customer/project naming feel real. */
    private const INDUSTRIES = [
        'Financial Services', 'Healthcare', 'Manufacturing', 'Retail',
        'Logistics', 'Legal', 'Education', 'Public Sector', 'Insurance',
        'Real Estate', 'Energy', 'Media & Publishing', 'Hospitality',
        'Pharmaceuticals', 'Telecommunications',
    ];

    /** Realistic corporate suffixes for company names. */
    private const COMPANY_SUFFIXES = [
        'GmbH', 'AG', 'Ltd.', 'Inc.', 'LLC', 'PLC', 'Holdings', 'Group',
        'Solutions', 'Systems', 'Partners', 'Industries', 'International',
    ];

    /** IT-services catalogue used to name realistic projects. */
    private const SERVICE_CATALOG = [
        'Microsoft 365 Migration',
        'Google Workspace Rollout',
        'Managed IT Support Onboarding',
        'Cybersecurity Risk Assessment',
        'ISO 27001 Readiness Audit',
        'Penetration Testing Engagement',
        'Network Infrastructure Refresh',
        'SD-WAN Deployment',
        'Firewall Migration to Fortinet',
        'AWS Cloud Migration',
        'Azure Landing Zone Setup',
        'Hybrid Cloud Architecture Design',
        'Backup & Disaster Recovery Implementation',
        'VoIP Phone System Implementation',
        'Microsoft Teams Voice Rollout',
        'Endpoint Security Rollout (CrowdStrike)',
        'Zero Trust Network Access Project',
        'VPN Modernization',
        'VMware to Proxmox Migration',
        'Server Virtualization Project',
        'Active Directory Hardening',
        'Identity & Access Management (Okta) Implementation',
        'ServiceNow ITSM Implementation',
        'Help Desk Outsourcing Onboarding',
        'Patch Management Automation',
        'SIEM Deployment (Splunk)',
        'Email Security Gateway Rollout',
        'Data Loss Prevention Program',
        'GDPR Compliance Review',
        'Mobile Device Management (Intune) Rollout',
        'Wi-Fi Site Survey & Refresh',
        'Database Performance Tuning Engagement',
    ];

    /** Sales-pipeline opportunity templates. */
    private const SALE_TEMPLATES = [
        'Annual Managed Support Contract',
        'Tier-2 Help Desk Expansion',
        'Cybersecurity Retainer',
        '24/7 NOC Monitoring Package',
        'Cloud Hosting Reseller Agreement',
        'Microsoft 365 Licensing Renewal',
        'Hardware Procurement Bundle',
        'Backup-as-a-Service Subscription',
        'SOC-as-a-Service Engagement',
        'Quarterly Security Audit',
    ];

    /** Curated tag set — name => hex colour. */
    private const TAGS = [
        'Enterprise'      => '#0d6efd',
        'SMB'             => '#6c757d',
        'Strategic'       => '#198754',
        'VIP'             => '#ffc107',
        'At Risk'         => '#dc3545',
        'Long-term'       => '#20c997',
        'MSP Plan'        => '#6610f2',
        'Healthcare'      => '#0dcaf0',
        'Public Sector'   => '#fd7e14',
        'Renewal Pending' => '#d63384',
    ];

    public function run(): void
    {
        // Resolve the Faker instance so we can use locale-flavoured data.
        /** @var Faker $faker */
        $faker = fake();

        $this->command?->info('Seeding demo data for an IT services company...');

        // Wrap everything in a transaction so a failure rolls back cleanly.
        DB::transaction(function () use ($faker) {
            $staff = $this->createStaffUsers($faker);
            $tags = $this->createTags();

            for ($i = 1; $i <= self::CUSTOMER_COUNT; $i++) {
                $customer = $this->createCustomer($faker);
                $this->attachTags($customer, $tags, $faker);
                $this->createContacts($customer, $faker);
                $this->createCustomerNotes($customer, $staff, $faker);
                $this->createCustomerFiles($customer, $staff, $faker);

                // Build a sales pipeline for this customer (1–3 opportunities).
                $sales = $this->createSales($customer, $staff, $faker);

                // Convert a portion of "won" sales into projects + contracts.
                $this->createProjectsAndContracts($customer, $sales, $staff, $faker);
            }
        });

        $this->command?->info(sprintf(
            'Done. Created ~%d customers with full sales/projects/contracts history.',
            self::CUSTOMER_COUNT,
        ));
    }

    // -----------------------------------------------------------------------
    // Internal staff (account managers, engineers, sales reps)
    // -----------------------------------------------------------------------

    /**
     * Create internal staff users that act as sales reps, project members
     * and note authors. The default admin user (id=1) is reused if present.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function createStaffUsers(Faker $faker)
    {
        $jobTitles = [
            'Account Manager', 'Senior Account Manager', 'Sales Engineer',
            'Solutions Architect', 'Service Delivery Manager',
            'Project Manager', 'Technical Consultant', 'Support Engineer',
            'Pre-Sales Consultant', 'Customer Success Manager',
        ];

        $staff = collect();

        for ($i = 0; $i < self::STAFF_COUNT; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $title = $faker->randomElement($jobTitles);

            // Use UserFactory for compliance with the project's existing
            // factory contract (hashed password, verified email, etc.).
            $staff->push(User::factory()->create([
                'name'     => "$firstName $lastName ($title)",
                'email'    => Str::lower($firstName . '.' . $lastName . '@clientverse-demo.test'),
                'password' => Hash::make('password'),
                'role'     => RoleEnum::USER->value,
                'is_active' => true,
            ]));
        }

        // Always include the existing admin (if any) so they own some records.
        $admin = User::where('role', RoleEnum::ADMIN->value)->first();
        if ($admin) {
            $staff->push($admin);
        }

        return $staff;
    }

    // -----------------------------------------------------------------------
    // Tags
    // -----------------------------------------------------------------------

    /**
     * Create the curated set of CRM tags (idempotent: re-uses existing rows).
     *
     * @return \Illuminate\Support\Collection<int, Tag>
     */
    private function createTags()
    {
        $tags = collect();

        foreach (self::TAGS as $name => $color) {
            $tags->push(Tag::firstOrCreate(['name' => $name], ['color' => $color]));
        }

        return $tags;
    }

    /**
     * Attach 1–3 random tags to a customer via the customer_tag pivot.
     */
    private function attachTags(Customer $customer, $tags, Faker $faker): void
    {
        $picked = $tags->random($faker->numberBetween(1, 3))->pluck('id')->all();
        $customer->tags()->syncWithoutDetaching($picked);
    }

    // -----------------------------------------------------------------------
    // Customers
    // -----------------------------------------------------------------------

    /**
     * Build a corporate-flavoured customer record with realistic metadata.
     */
    private function createCustomer(Faker $faker): Customer
    {
        $industry = $faker->randomElement(self::INDUSTRIES);
        $companyBase = $faker->lastName() . ' ' . $faker->randomElement([
            'Consulting', 'Capital', 'Logistics', 'Industries', 'Group',
            'Holdings', 'Technologies', 'Partners', 'Health', 'Manufacturing',
        ]);
        $company = $companyBase . ' ' . $faker->randomElement(self::COMPANY_SUFFIXES);

        // Status weighting: most demo customers are active accounts.
        $status = $faker->randomElement([
            'active', 'active', 'active', 'active', 'lead', 'lead', 'inactive',
        ]);

        $currency = $faker->randomElement(['USD', 'EUR', 'GBP', 'CHF']);

        $address = sprintf(
            "%s\n%s %s\n%s",
            $faker->streetAddress(),
            $faker->postcode(),
            $faker->city(),
            $faker->country(),
        );

        return Customer::create([
            'name'            => $company,
            'email'           => 'contact@' . Str::slug(Str::before($companyBase, ' '), '') . '.example.com',
            'phone'           => $faker->phoneNumber(),
            'website'         => 'https://www.' . Str::slug($companyBase, '') . '.example.com',
            'company'         => $company,
            'address'         => $address,
            'billing_address' => $address,
            'vat_id'          => strtoupper($faker->lexify('??')) . $faker->numerify('#########'),
            'currency'        => $currency,
            'type'            => 'company',
            'status'          => $status,
            'notes'           => sprintf(
                '%s account in the %s sector. Primary engagement: managed IT services and ongoing technical support.',
                $company,
                $industry,
            ),
            'metadata' => [
                'industry'           => $industry,
                'employee_headcount' => $faker->numberBetween(25, 5000),
                'annual_revenue_eur' => $faker->numberBetween(500_000, 250_000_000),
                'account_tier'       => $faker->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum']),
                'onboarded_at'       => $faker->dateTimeBetween('-4 years', '-1 month')->format('Y-m-d'),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Contacts
    // -----------------------------------------------------------------------

    /**
     * Create 2–5 contacts per customer with one decision maker + one finance
     * contact + one technical contact at minimum to mirror real B2B accounts.
     */
    private function createContacts(Customer $customer, Faker $faker): void
    {
        $companySlug = Str::slug(Str::before($customer->company, ' '), '');
        $domain = $companySlug . '.example.com';

        // Required corporate contact roles in a typical enterprise account.
        $required = [
            ['role' => 'decision_maker', 'position' => $faker->randomElement(['CIO', 'CTO', 'IT Director', 'Head of IT'])],
            ['role' => 'finance',        'position' => $faker->randomElement(['CFO', 'Finance Manager', 'Accounts Payable Lead'])],
            ['role' => 'technical',      'position' => $faker->randomElement(['IT Manager', 'Systems Administrator', 'DevOps Lead', 'Network Engineer'])],
        ];

        // Add a few "other" contacts for realism (procurement, exec assistants).
        $extraCount = $faker->numberBetween(0, 2);
        for ($i = 0; $i < $extraCount; $i++) {
            $required[] = [
                'role'     => 'other',
                'position' => $faker->randomElement(['Procurement Officer', 'Operations Manager', 'Executive Assistant']),
            ];
        }

        foreach ($required as $idx => $spec) {
            $first = $faker->firstName();
            $last = $faker->lastName();

            Contact::create([
                'customer_id'       => $customer->id,
                'first_name'        => $first,
                'last_name'         => $last,
                'email'             => Str::lower("$first.$last@$domain"),
                'phone'             => $faker->phoneNumber(),
                'position'          => $spec['position'],
                'role'              => $spec['role'],
                // First contact in the list is the primary contact.
                'is_primary'        => $idx === 0,
                // Finance + decision makers occasionally get portal access.
                'has_portal_access' => $idx === 0 && $faker->boolean(40),
                'notes'             => $faker->boolean(30)
                    ? 'Prefers email communication. Available during business hours (CET).'
                    : null,
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Customer notes (CRM activity log)
    // -----------------------------------------------------------------------

    private function createCustomerNotes(Customer $customer, $staff, Faker $faker): void
    {
        $templates = [
            'Quarterly business review completed. Customer is satisfied with the SLA response times.',
            'Discussed expansion to a 24/7 monitoring plan. Follow-up scheduled.',
            'Renewal conversation scheduled for next quarter. Currently on the Gold tier.',
            'Reported intermittent VPN drops at the head office; ticket #%TICKET% opened with engineering.',
            'Onboarded two new technical contacts after internal restructuring on the customer side.',
            'Customer requested an additional security audit ahead of their ISO 27001 recertification.',
            'Procurement updated the billing address; invoice template adjusted accordingly.',
            'Escalation from the CIO regarding latency in the Azure tenant. Resolved within SLA.',
        ];

        $noteCount = $faker->numberBetween(1, 4);

        for ($i = 0; $i < $noteCount; $i++) {
            $template = $faker->randomElement($templates);
            $content = str_replace('%TICKET%', (string) $faker->numberBetween(10000, 99999), $template);

            CustomerNote::create([
                'customer_id' => $customer->id,
                'user_id'     => $staff->random()->id,
                'content'     => $content,
            ])->forceFill([
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => Carbon::now(),
            ])->save();
        }
    }

    // -----------------------------------------------------------------------
    // Files (morph) - metadata only, no actual blobs written to storage
    // -----------------------------------------------------------------------

    private function createCustomerFiles(Customer $customer, $staff, Faker $faker): void
    {
        $candidates = [
            ['NDA_Signed.pdf',                    'application/pdf'],
            ['MSA_Master_Services_Agreement.pdf', 'application/pdf'],
            ['Network_Diagram.png',               'image/png'],
            ['Asset_Inventory.xlsx',              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['Onboarding_Checklist.docx',         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];

        $count = $faker->numberBetween(0, 3);
        $picked = $faker->randomElements($candidates, $count);

        foreach ($picked as [$name, $mime]) {
            File::create([
                'fileable_id'   => $customer->id,
                'fileable_type' => Customer::class,
                'uploaded_by'   => $staff->random()->id,
                'original_name' => $name,
                // Stored name is a synthetic UUID-prefixed filename — purely
                // demo metadata; no physical file is written to disk.
                'stored_name'   => Str::uuid()->toString() . '_' . $name,
                'mime_type'     => $mime,
                'size'          => $faker->numberBetween(20_000, 5_000_000),
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Sales pipeline
    // -----------------------------------------------------------------------

    /**
     * Create 1–3 sales opportunities for the customer. Stage probability is
     * coupled to the chosen stage (e.g. "won" = 100, "lost" = 0).
     *
     * @return \Illuminate\Support\Collection<int, Sale>
     */
    private function createSales(Customer $customer, $staff, Faker $faker)
    {
        $sales = collect();
        $count = $faker->numberBetween(1, 3);

        for ($i = 0; $i < $count; $i++) {
            $stage = $faker->randomElement([
                'lead', 'qualified', 'proposal_sent',
                'won', 'won', 'won', // bias toward "won" so we get projects
                'lost',
            ]);

            // Probability is meaningful only for in-flight opportunities.
            $probability = match ($stage) {
                'lead'          => $faker->numberBetween(5, 20),
                'qualified'     => $faker->numberBetween(25, 50),
                'proposal_sent' => $faker->numberBetween(55, 80),
                'won'           => 100,
                'lost'          => 0,
            };

            $template = $faker->randomElement(self::SALE_TEMPLATES);

            $sales->push(Sale::create([
                'customer_id'         => $customer->id,
                'assigned_to'         => $staff->random()->id,
                'name'                => $template . ' - ' . $customer->company,
                'value'               => $faker->numberBetween(5_000, 250_000),
                'currency'            => $customer->currency,
                'stage'               => $stage,
                'probability'         => $probability,
                'expected_close_date' => $faker->dateTimeBetween('-6 months', '+9 months')->format('Y-m-d'),
                'notes'               => sprintf(
                    'Opportunity sourced via %s. Decision committee includes the CIO and CFO.',
                    $faker->randomElement(['inbound referral', 'partner channel', 'cold outreach', 'existing account expansion']),
                ),
            ]));
        }

        return $sales;
    }

    // -----------------------------------------------------------------------
    // Projects, milestones and contracts
    // -----------------------------------------------------------------------

    /**
     * Convert "won" sales into delivery projects + signed contracts.
     * Customers with no won sales still get one internal advisory project so
     * every account has at least some delivery history.
     */
    private function createProjectsAndContracts(Customer $customer, $sales, $staff, Faker $faker): void
    {
        $wonSales = $sales->where('stage', 'won')->values();

        // Always guarantee at least one project per customer for demo richness.
        if ($wonSales->isEmpty()) {
            $project = $this->createProject($customer, null, $staff, $faker);
            $this->createMilestones($project, $faker);
            $this->createContract($customer, $project, null, $faker);
            return;
        }

        foreach ($wonSales as $sale) {
            $project = $this->createProject($customer, $sale, $staff, $faker);
            $this->createMilestones($project, $faker);
            $this->createContract($customer, $project, $sale, $faker);
            $this->createProjectFiles($project, $staff, $faker);
        }
    }

    /**
     * Build a single project for a customer, optionally linked to a sale.
     */
    private function createProject(Customer $customer, ?Sale $sale, $staff, Faker $faker): Project
    {
        $serviceName = $faker->randomElement(self::SERVICE_CATALOG);
        $status = $faker->randomElement(ProjectStatusEnum::values());

        // Date logic respects the chosen status:
        //  - planned   => starts in the future
        //  - active    => started recently, due in the future
        //  - on_hold   => started in the past, due-date may have slipped
        //  - completed => fully in the past
        [$startDate, $dueDate] = $this->datesForStatus($status, $faker);

        $project = Project::create([
            'customer_id' => $customer->id,
            'name'        => $serviceName . ' - ' . $customer->company,
            'description' => sprintf(
                'Delivery of "%s" for %s. Scope agreed during the pre-sales phase and tracked via the linked contract and milestones.',
                $serviceName,
                $customer->company,
            ),
            'start_date'  => $startDate,
            'due_date'    => $dueDate,
            'status'      => $status,
            'visibility'  => $faker->boolean(70) ? 'shared' : 'internal',
            'notes'       => $sale
                ? 'Originated from sales opportunity #' . $sale->id . '.'
                : 'Internal advisory engagement (no linked opportunity).',
        ]);

        // Assign 2–4 staff members to the project team.
        $teamIds = $staff->random(min($faker->numberBetween(2, 4), $staff->count()))->pluck('id')->all();
        $project->members()->syncWithoutDetaching($teamIds);

        return $project;
    }

    /**
     * Create 3–5 milestones per project, marking past ones as completed.
     */
    private function createMilestones(Project $project, Faker $faker): void
    {
        $templates = [
            'Kick-off & Discovery Workshop',
            'Solution Design Sign-off',
            'Pilot Deployment',
            'User Acceptance Testing',
            'Production Rollout',
            'Go-Live Hypercare',
            'Project Closure & Handover',
        ];

        $picked = $faker->randomElements($templates, $faker->numberBetween(3, 5));
        $start = $project->start_date ?? Carbon::now();
        $end = $project->due_date ?? Carbon::now()->addMonths(3);

        $count = count($picked);
        foreach ($picked as $i => $name) {
            // Spread milestones evenly between the project's start and due date.
            $ratio = ($i + 1) / ($count + 1);
            $dueDate = Carbon::parse($start)->addDays(
                (int) (Carbon::parse($start)->diffInDays($end) * $ratio),
            );

            Milestone::create([
                'project_id'   => $project->id,
                'name'         => $name,
                'description'  => 'Standard delivery milestone for the engagement.',
                'due_date'     => $dueDate->format('Y-m-d'),
                'is_completed' => $dueDate->isPast(),
                'notes'        => $dueDate->isPast() ? 'Closed out per delivery checklist.' : null,
            ]);
        }
    }

    /**
     * Generate a contract that ties together customer + project + sale.
     */
    private function createContract(Customer $customer, Project $project, ?Sale $sale, Faker $faker): void
    {
        $type = $faker->randomElement(['fixed', 'recurring']);
        $status = $faker->randomElement([
            'draft', 'active', 'active', 'active', 'expired', 'cancelled',
        ]);

        $start = Carbon::parse($project->start_date ?? Carbon::now()->subMonths(3));
        $end = $type === 'recurring'
            ? $start->copy()->addYear()
            : Carbon::parse($project->due_date ?? $start->copy()->addMonths(6));

        Contract::create([
            'customer_id' => $customer->id,
            'project_id'  => $project->id,
            'sale_id'     => $sale?->id,
            'title'       => sprintf(
                '%s Agreement - %s',
                $type === 'recurring' ? 'Managed Services' : 'Project Delivery',
                $project->name,
            ),
            'description' => sprintf(
                'Contract governing the "%s" engagement, including SLAs, payment terms and acceptance criteria.',
                $project->name,
            ),
            'value'       => $sale?->value ?? $faker->numberBetween(8_000, 180_000),
            'currency'    => $customer->currency,
            'type'        => $type,
            'status'      => $status,
            'start_date'  => $start->format('Y-m-d'),
            'end_date'    => $end->format('Y-m-d'),
            // Active/expired contracts are signed; drafts are not yet signed.
            'signed_date' => in_array($status, ['active', 'expired'], true)
                ? $start->copy()->subDays($faker->numberBetween(1, 14))->format('Y-m-d')
                : null,
            'notes'       => $type === 'recurring'
                ? 'Auto-renews annually unless cancelled with 60 days notice.'
                : 'Fixed-price engagement billed against milestone acceptance.',
        ]);
    }

    /**
     * Attach a couple of demo files to the project (metadata only).
     */
    private function createProjectFiles(Project $project, $staff, Faker $faker): void
    {
        $candidates = [
            ['Statement_of_Work.pdf',  'application/pdf'],
            ['Project_Plan.xlsx',      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['Architecture_Diagram.png', 'image/png'],
            ['Runbook.docx',           'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];

        foreach ($faker->randomElements($candidates, $faker->numberBetween(1, 3)) as [$name, $mime]) {
            File::create([
                'fileable_id'   => $project->id,
                'fileable_type' => Project::class,
                'uploaded_by'   => $staff->random()->id,
                'original_name' => $name,
                'stored_name'   => Str::uuid()->toString() . '_' . $name,
                'mime_type'     => $mime,
                'size'          => $faker->numberBetween(50_000, 8_000_000),
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Pick coherent start_date / due_date pairs given a project status.
     *
     * @return array{0: string, 1: string} [start_date, due_date] in Y-m-d
     */
    private function datesForStatus(string $status, Faker $faker): array
    {
        return match ($status) {
            ProjectStatusEnum::PLANNED->value => [
                $faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
                $faker->dateTimeBetween('+3 months', '+9 months')->format('Y-m-d'),
            ],
            ProjectStatusEnum::ACTIVE->value => [
                $faker->dateTimeBetween('-3 months', '-1 week')->format('Y-m-d'),
                $faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            ],
            ProjectStatusEnum::ON_HOLD->value => [
                $faker->dateTimeBetween('-9 months', '-3 months')->format('Y-m-d'),
                $faker->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d'),
            ],
            ProjectStatusEnum::COMPLETED->value => [
                $faker->dateTimeBetween('-2 years', '-9 months')->format('Y-m-d'),
                $faker->dateTimeBetween('-8 months', '-1 month')->format('Y-m-d'),
            ],
        };
    }
}
