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
@foreach($announcements as $announcement)
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-megaphone text-primary me-2"></i>
                <h2 class="h6 mb-0 flex-grow-1 text-truncate">{{ $announcement->title }}</h2>
                <small class="text-muted ms-2">{{ $announcement->created_at->format('M d, Y') }}</small>
            </div>
            <div class="announcement-body">{{ $announcement->body_html }}</div>
        </div>
    </div>
@endforeach
