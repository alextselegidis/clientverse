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

namespace App\Http\Controllers\Api\V1;

use App\Models\Project;
use Orion\Http\Controllers\Controller;

class ProjectsApiV1Controller extends Controller
{
    protected $model = Project::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['name', 'status', 'visibility', 'customer_id', 'start_date', 'due_date', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['name', 'status', 'visibility', 'start_date', 'due_date', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['name', 'description', 'notes'];
    }

    public function includes(): array
    {
        return ['customer', 'milestones'];
    }
}
