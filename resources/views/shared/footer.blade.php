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

<div class="container">
    <div class="row">
        <div class="col">
            <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 px-0 mt-3 border-top small">
                <div class="col-md-4 ps-0 d-flex align-items-center">
                    <a href="https://github.com/alextselegidis/clientverse" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1" target="_blank">
                        <img src="images/logo.png" alt="logo" class="footer-logo" style="width: 16px; height: 16px;"/>
                    </a>
                    <span class="mb-3 mb-md-0 text-body-secondary">
                        <a href="https://github.com/alextselegidis/clientverse" class="text-decoration-none me-2" target="_blank">Clientverse</a>
                        v{{config('app.version')}}
                    </span>
                </div>

                <div class="col-md-4 pe-0 d-flex align-items-center justify-content-md-end">
                    <a href="https://alextselegidis.com" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1" target="_blank">
                        <img src="images/alextselegidis-logo-16x16.png" alt="logo"/>
                    </a>
                    <span class="mb-3 mb-md-0 text-body-secondary">
                        <a href="https://alextselegidis.com" class="text-decoration-none" target="_blank">
                            Alex Tselegidis
                        </a>
                        © {{date('Y')}} - Software Services
                    </span>
                </div>

            </footer>
        </div>
    </div>
</div>
