{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */
--}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle select all checkboxes
    document.querySelectorAll('.bulk-select-all').forEach(function(selectAll) {
        const tableId = selectAll.dataset.table;
        const checkboxes = document.querySelectorAll(`.bulk-select-item[data-table="${tableId}"]`);
        const bulkActions = document.getElementById(`bulk-actions-${tableId}`);
        const form = document.getElementById(`bulk-delete-form-${tableId}`);

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateBulkActions(tableId);
        });

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
                updateBulkActions(tableId);
            });
        });
    });

    function updateBulkActions(tableId) {
        const checkboxes = document.querySelectorAll(`.bulk-select-item[data-table="${tableId}"]:checked`);
        const bulkActions = document.getElementById(`bulk-actions-${tableId}`);
        const form = document.getElementById(`bulk-delete-form-${tableId}`);
        const countElements = bulkActions.querySelectorAll('.selected-count');
        const modalCountElements = document.querySelectorAll(`#bulk-delete-modal-${tableId} .selected-count`);

        if (checkboxes.length > 0) {
            bulkActions.classList.remove('d-none');
            bulkActions.classList.add('d-flex');
        } else {
            bulkActions.classList.add('d-none');
            bulkActions.classList.remove('d-flex');
        }

        countElements.forEach(el => el.textContent = checkboxes.length);
        modalCountElements.forEach(el => el.textContent = checkboxes.length);

        // Update form with selected IDs
        const existingInputs = form.querySelectorAll('input[name="ids[]"]');
        existingInputs.forEach(input => input.remove());

        checkboxes.forEach(function(checkbox) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
    }
});
</script>
