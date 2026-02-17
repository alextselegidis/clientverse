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

<div class="bulk-actions d-none align-items-center gap-2" id="bulk-actions-{{ $tableId }}">
    <span class="text-muted small">
        <span class="selected-count">0</span> {{ __('selected') }}
    </span>
    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bulk-delete-modal-{{ $tableId }}">
        <i class="bi bi-trash me-1"></i>{{ __('delete') }}
    </button>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulk-delete-modal-{{ $tableId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('bulk_delete_confirm_message') }}</p>
                <p class="text-danger fw-medium mb-0">
                    <span class="selected-count">0</span> {{ __('records_will_be_deleted') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <form action="{{ $route }}" method="POST" id="bulk-delete-form-{{ $tableId }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
