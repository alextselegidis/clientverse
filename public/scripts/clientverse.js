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

// Auto-focus the first input of any modal

document.querySelectorAll('.modal').forEach(function (modal) {
    modal.addEventListener('shown.bs.modal', (event) => {
        event.target.querySelector('input:not([type="hidden"])')?.focus();
    });
});

// Close other table row dropdowns when one opens

document.addEventListener('show.bs.dropdown', function (event) {
    // Check if this dropdown is inside a table
    const table = event.target.closest('table');
    if (table) {
        // Find all other open dropdowns in this table and close them
        const openDropdowns = table.querySelectorAll('.dropdown-menu.show');
        openDropdowns.forEach(function (dropdown) {
            // Skip if it's the same dropdown being opened
            if (dropdown !== event.target.nextElementSibling) {
                const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
                if (dropdownInstance) {
                    dropdownInstance.hide();
                }
            }
        });
    }
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

// Repeatable form rows: <button data-add-row="template-id" data-target="tbody-id">.
// Every "__INDEX__" in the template becomes a fresh index so input names stay unique.
document.querySelectorAll('[data-add-row]').forEach(function (button) {
    const template = document.getElementById(button.dataset.addRow);
    const target = document.getElementById(button.dataset.target);
    let index = target.children.length;

    button.addEventListener('click', function () {
        const html = template.innerHTML.replaceAll('__INDEX__', String(index++));
        target.insertAdjacentHTML('beforeend', html);
        target.lastElementChild.querySelector('input')?.focus();
    });
});

// Picking a catalog item in a line's description fills its price and tax.
document.querySelectorAll('[data-catalog]').forEach(function (tbody) {
    const catalog = document.getElementById(tbody.dataset.catalog);

    tbody.addEventListener('change', function (event) {
        if (!event.target.name?.endsWith('[description]')) {
            return;
        }

        const option = Array.from(catalog.options).find((option) => option.value === event.target.value);
        const row = event.target.closest('tr');

        if (option) {
            row.querySelector('[name$="[unit_price]"]').value = option.dataset.unitPrice;
            row.querySelector('[name$="[tax_rate]"]').value = option.dataset.taxRate;
        }
    });
});
