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

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<base href="{{url('')}}/">

<title>@yield('pageTitle') | Clientverse</title>
<meta name="description" content="Clientverse is a self-hosted CRM for managing customers, contacts, projects, sales and contracts in one place.">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#1a1d23" media="(prefers-color-scheme: dark)">

{{-- Set the theme and the collapsed sidebar before the first paint, so neither flashes. --}}
<script>
    (function () {
        var root = document.documentElement;
        var theme = @json(auth()->user()?->theme ?? 'auto');

        if (theme !== 'light' && theme !== 'dark') {
            theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        root.setAttribute('data-bs-theme', theme);
        root.dataset.searchLabel = @json(__('search'));

        try {
            if (localStorage.getItem('cv-sidebar') === 'collapsed') {
                root.classList.add('sidebar-collapsed');
            }
        } catch (e) {
        }
    })();
</script>

<link rel="icon" href="favicon.ico" type="image/x-icon"/>
<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
<link rel="manifest" href="manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Clientverse">

<link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="vendor/bootstrap-icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="styles/clientverse.css?{{config('app.version')}}">

{{-- Load a page while the pointer rests on its link, so the click feels instant. Laravel ignores
     prefetch requests when tracking the previous URL, so redirect()->back() is unaffected. --}}
<script type="speculationrules">
    {
        "prefetch": [{
            "where": {
                "and": [
                    {"href_matches": "/*"},
                    {"not": {"href_matches": ["/*/download", "/*/print", "/*.csv", "/logout"]}},
                    {"not": {"selector_matches": "[download], [target=_blank], [data-no-prefetch]"}}
                ]
            },
            "eagerness": "moderate"
        }]
    }
</script>
