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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A reusable priced line. Lines copy its values when picked, so changing an
 * item never rewrites an invoice that was already issued.
 */
class Item extends Model
{
    protected $fillable = ['description', 'unit_price', 'tax_rate'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];
}
