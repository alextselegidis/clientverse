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

use App\Models\Milestone;
use Orion\Http\Controllers\Controller;

class MilestonesApiV1Controller extends Controller
{
    protected $model = Milestone::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['name', 'is_completed', 'project_id', 'due_date', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['name', 'is_completed', 'due_date', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['name', 'description'];
    }

    public function includes(): array
    {
        return ['project'];
    }
}
