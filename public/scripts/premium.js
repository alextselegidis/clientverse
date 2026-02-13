/* ----------------------------------------------------------------------------
 * Clientverse - Powerful CRM System
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

// Auto-hide alert

document.querySelectorAll('.toast').forEach(function (toastEl) {
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 3000,
    });
    toast.show();
});

// Auto-focus create modal input

document
    .getElementById('create-modal')
    .addEventListener('shown.bs.modal', (event) => event.target.querySelector('input:not([type="hidden"])').focus());

// Init all dropdowns

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select').forEach(function (el) {
        new Choices(el, {
            shouldSort: false,
            removeItemButton: el.multiple, // Only show remove buttons for multi-selects
        });
    });
});
