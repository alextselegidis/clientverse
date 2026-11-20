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
class CreditNoteItem extends InvoiceItem
{
    protected $table = 'credit_note_items';

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }
}
