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
 * Route middleware: CheckPermission::class.':invoices'. The action comes from the
 * HTTP verb - reads are "view", DELETE is "delete", every other write is "edit".
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $action = match (true) {
            $request->isMethodSafe(), str_ends_with($request->path(), '/search') => 'view',
            $request->isMethod('DELETE') => 'delete',
            default => 'edit',
        };

        if (!$request->user()?->hasPermission($module, $action)) {
            abort(403);
        }

        return $next($request);
    }
}
