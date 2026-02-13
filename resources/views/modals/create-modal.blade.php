{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
--}}

{{--
    @var string $route Target store route
    @var string $title Modal title
    @var string $input_name The model name to be typed
    @var string $input_type The type of input for the field
--}}

<div class="modal" tabindex="-1" id="create-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="{{$route}}" method="POST">
                @csrf
                @method('POST')

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{$title ?? __('create')}}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="{{$input_name ?? 'name'}}" class="form-label">
                            {{ __($input_name ?? 'name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="{{$input_type ?? 'text'}}"
                               id="{{$input_name ?? 'name'}}"
                               name="{{$input_name ?? 'name'}}"
                               class="form-control"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="bg-light justify-content-center modal-footer">
                    <a class="btn btn-secondary" data-bs-dismiss="modal">
                        {{__('close')}}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{__('save')}}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
