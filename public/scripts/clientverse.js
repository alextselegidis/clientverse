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

// Unsaved changes warning for forms

(function () {
    const trackedForms = document.querySelectorAll('form[id]:not([data-no-unsaved-warning])');

    if (!trackedForms.length) {
        return;
    }

    let formChanged = false;
    let isSubmitting = false;

    /**
     * Store initial form state for comparison
     */
    function getFormData(form) {
        const formData = new FormData(form);
        const data = {};
        for (const [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        return JSON.stringify(data);
    }

    const initialFormStates = new Map();

    trackedForms.forEach(function (form) {
        // Skip forms inside modals (create modals, etc.)
        if (form.closest('.modal')) {
            return;
        }

        // Store initial state
        initialFormStates.set(form, getFormData(form));

        // Listen for changes on form inputs
        form.addEventListener('input', function () {
            const currentState = getFormData(form);
            const initialState = initialFormStates.get(form);
            formChanged = currentState !== initialState;
        });

        form.addEventListener('change', function () {
            const currentState = getFormData(form);
            const initialState = initialFormStates.get(form);
            formChanged = currentState !== initialState;
        });

        // Mark as submitting when form is submitted
        form.addEventListener('submit', function () {
            isSubmitting = true;
        });
    });

    // Also handle submit buttons that reference forms via form attribute
    document.querySelectorAll('button[type="submit"][form]').forEach(function (button) {
        button.addEventListener('click', function () {
            isSubmitting = true;
        });
    });

    // Warn before leaving page with unsaved changes
    window.addEventListener('beforeunload', function (event) {
        if (formChanged && !isSubmitting) {
            event.preventDefault();
            event.returnValue = '';
            return '';
        }
    });
})();
