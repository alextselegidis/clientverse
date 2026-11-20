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

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API requests can pull related records in with ?include= (or "includes" in a
 * search body). A relation that belongs to another module needs that module's
 * view permission, or a customers-only user could read deals through a customer.
 */
class CheckApiIncludes
{
    private const MODULES = [
        'customer' => 'customers', 'contacts' => 'customers', 'customerNotes' => 'customers',
        'projects' => 'projects', 'project' => 'projects', 'milestones' => 'projects',
        'contracts' => 'contracts', 'sales' => 'sales', 'sale' => 'sales',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $requested = collect(explode(',', (string) $request->query('include', '')))
            ->merge(collect($request->input('includes', []))->pluck('relation'))
            ->filter()
            ->flatMap(fn ($relation) => explode('.', (string) $relation));

        foreach ($requested as $relation) {
            $module = self::MODULES[$relation] ?? null;

            if ($module && !$request->user()?->hasPermission($module)) {
                abort(403);
            }
        }

        return $next($request);
    }
}
