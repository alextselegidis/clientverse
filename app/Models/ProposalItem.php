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
class ProposalItem extends InvoiceItem
{
    protected $table = 'proposal_items';

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
