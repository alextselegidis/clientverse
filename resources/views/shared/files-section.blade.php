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

{{-- Files Section Partial --}}
{{-- Required variables: $model, $uploadRoute, $downloadRoute, $deleteRoute, $uploadLimits --}}

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('files') }}</h5>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#uploadForm">
            <i class="bi bi-upload me-1"></i> {{ __('upload') }}
        </button>
    </div>
    <div class="card-body">
        <!-- Upload Form (Collapsed by default) -->
        <div class="collapse mb-4" id="uploadForm">
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>{{ __('upload_limits') }}:</strong>
                {{ __('max_file_size') }}: {{ $uploadLimits['max_file_size_formatted'] }} |
                {{ __('max_files') }}: {{ $uploadLimits['max_files'] }} |
                {{ __('allowed_types') }}: {{ implode(', ', $uploadLimits['allowed_extensions']) }}
            </div>
            <form action="{{ $uploadRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="files[]" class="form-control" multiple
                           accept=".{{ implode(',.', $uploadLimits['allowed_extensions']) }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload me-1"></i> {{ __('upload_files') }}
                </button>
            </form>
        </div>

        <!-- Files List -->
        @if($model->files->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('file') }}</th>
                            <th>{{ __('size') }}</th>
                            <th>{{ __('uploaded') }}</th>
                            <th class="text-end" style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($model->files as $file)
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark me-2 text-muted"></i>
                                    <a href="{{ route($downloadRoute, array_merge($routeParams ?? [], ['file' => $file->id])) }}">
                                        {{ $file->original_name }}
                                    </a>
                                </td>
                                <td>{{ $file->formatted_size }}</td>
                                <td>{{ $file->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route($downloadRoute, array_merge($routeParams ?? [], ['file' => $file->id])) }}"
                                       class="btn btn-sm btn-outline-secondary me-1" title="{{ __('download') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form action="{{ route($deleteRoute, array_merge($routeParams ?? [], ['file' => $file->id])) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ __('delete_file_prompt') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-folder2-open display-6 d-block mb-2"></i>
                {{ __('no_files_found') }}
            </div>
        @endif
    </div>
</div>
