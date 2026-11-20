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

// ponytail: same columns and maths as an invoice line, only the table differs.
class EstimateItem extends InvoiceItem
{
    protected $table = 'estimate_items';

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
