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

// Sales board: dropping a card on a column submits that card's stage form.
document.querySelectorAll('.sales-board').forEach(function (board) {
    let dragged = null;

    board.addEventListener('dragstart', function (event) {
        dragged = event.target.closest('[data-sale]');
    });

    board.querySelectorAll('[data-stage]').forEach(function (column) {
        column.addEventListener('dragover', function (event) {
            event.preventDefault();
            column.classList.add('is-drop-target');
        });

        column.addEventListener('dragleave', function () {
            column.classList.remove('is-drop-target');
        });

        column.addEventListener('drop', function (event) {
            event.preventDefault();
            column.classList.remove('is-drop-target');

            const select = dragged?.querySelector('select[name="stage"]');

            if (select && select.value !== column.dataset.stage) {
                select.value = column.dataset.stage;
                select.form.submit();
            }
        });
    });
});

// A form posts once: a second click while the first request is on its way is ignored.
// Search forms (GET) and forms opening a new tab are left alone.
document.addEventListener('submit', function (event) {
    const form = event.target;

    if (event.defaultPrevented || form.method !== 'post' || form.target) {
        return;
    }

    if (form.classList.contains('is-submitting')) {
        event.preventDefault();
        return;
    }

    form.classList.add('is-submitting');
    sessionStorage.setItem('cv-scroll', JSON.stringify({path: location.pathname, y: scrollY, at: Date.now()}));
});

// Coming back with the browser's back button restores the page as it was, submit lock included.
window.addEventListener('pageshow', function () {
    document.querySelectorAll('form.is-submitting').forEach((form) => form.classList.remove('is-submitting'));
});

// After a save that lands back on the same page, return to where the user was instead of the top.
(function () {
    const saved = JSON.parse(sessionStorage.getItem('cv-scroll') || 'null');
    sessionStorage.removeItem('cv-scroll');

    if (saved && saved.path === location.pathname && Date.now() - saved.at < 10000) {
        scrollTo(0, saved.y);
    }
})();

// List search runs as you type: the filter form submits after a short pause and the
// reloaded page puts the cursor back at the end of the search box.
document.querySelectorAll('.table-filters input[name="q"]').forEach(function (input) {
    let timer;

    if (sessionStorage.getItem('cv-search') === location.pathname) {
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }

    sessionStorage.removeItem('cv-search');

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            sessionStorage.setItem('cv-search', location.pathname);
            input.form.requestSubmit();
        }, 400);
    });
});

// "/" or Ctrl/Cmd+K opens global search from anywhere except while typing in a field.
document.addEventListener('keydown', function (event) {
    const modal = document.getElementById('search-modal');
    const typing = event.target.closest('input, textarea, select, [contenteditable="true"]');
    const shortcut = (event.key === '/' && !typing) || (event.key === 'k' && (event.metaKey || event.ctrlKey));

    if (modal && shortcut && !event.altKey && !event.defaultPrevented && !event.target.closest('.cv-select')) {
        event.preventDefault();
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});

// Spotlight: the search dialog lists the sidebar pages that match at once and, after a short pause, the
// records /search finds (read from its HTML, so there is one search implementation). Arrow keys move the
// highlight and Enter opens it; Enter with nothing highlighted runs the full search as before.
document.querySelectorAll('.app-spotlight').forEach(function (results) {
    const form = results.closest('form');
    const input = form.querySelector('input[name="q"]');
    let timer;
    let request = 0;
    let pageLinks = [];

    function iconOf(element) {
        const icon = element.querySelector('i');
        return icon ? [...icon.classList].find((name) => name.startsWith('bi-')) : 'bi-arrow-right';
    }

    function item(href, icon, label, code) {
        const link = document.createElement('a');
        const i = document.createElement('i');
        const text = document.createElement('span');
        link.href = href;
        link.className = 'list-group-item list-group-item-action d-flex align-items-center';
        i.className = 'bi ' + icon + ' me-2';
        text.className = 'text-truncate flex-grow-1';
        text.textContent = label;
        link.append(i, text);
        if (code) {
            const badge = document.createElement('span');
            badge.className = 'app-spotlight-code ms-3';
            badge.textContent = code;
            link.append(badge);
        }
        return link;
    }

    function render(groups) {
        const term = input.value.trim();
        results.replaceChildren();
        groups.forEach(function ([label, links]) {
            if (!links.length) {
                return;
            }
            const heading = document.createElement('div');
            heading.className = 'app-spotlight-heading';
            heading.textContent = label;
            results.append(heading, ...links);
        });
        results.hidden = !results.children.length;
    }

    input.addEventListener('input', function () {
        const term = input.value.trim();
        pageLinks = !term ? [] : [...document.querySelectorAll('#sidebar nav a[href]')]
            .filter((a) => a.textContent.trim().toLowerCase().includes(term.toLowerCase()))
            .map((a) => item(a.href, iconOf(a), a.textContent.trim()));
        render([[results.dataset.pagesLabel, pageLinks]]);

        clearTimeout(timer);
        const current = ++request;
        if (term.length < 2) {
            return;
        }
        timer = setTimeout(function () {
            fetch(form.action + '?' + new URLSearchParams({q: term}))
                .then((response) => response.text().then((html) => [response, html]))
                .then(function ([response, html]) {
                    if (current !== request) {
                        return;
                    }
                    // /search redirects a record code (C0042) to the record itself.
                    if (response.redirected) {
                        const title = new DOMParser().parseFromString(html, 'text/html').querySelector('.app-page-title');
                        render([[term.toUpperCase(), [item(response.url, 'bi-arrow-right', title ? title.textContent.trim() : response.url)]]]);
                        return;
                    }
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    // Each result list on the search page follows its shared/search-section heading.
                    const sections = [...doc.querySelectorAll('.app-content .list-group')].map(function (list) {
                        const heading = list.previousElementSibling.querySelector('h5');
                        const label = [...heading.childNodes].filter((node) => node.nodeType === Node.TEXT_NODE)
                            .map((node) => node.textContent).join('').trim();
                        // ponytail: first 5 per section; the full search entry covers the rest.
                        const links = [...list.querySelectorAll('a.list-group-item')].slice(0, 5)
                            .map((a) => item(a.getAttribute('href'), iconOf(heading), a.querySelector('strong').textContent.trim(), a.dataset.code));
                        return [label, links];
                    });
                    render([[results.dataset.pagesLabel, pageLinks], ...sections]);
                })
                .catch(() => {});
        }, 200);
    });

    input.addEventListener('keydown', function (event) {
        const links = [...results.querySelectorAll('a')];
        let index = links.findIndex((a) => a.classList.contains('active'));

        // Enter opens a result only once the arrows picked one; otherwise the form submits to the search page.
        if (event.key === 'Enter' && index >= 0) {
            event.preventDefault();
            location.href = links[index].href;
            return;
        }
        if (!links.length || !['ArrowDown', 'ArrowUp'].includes(event.key)) {
            return;
        }
        event.preventDefault();
        links[index]?.classList.remove('active');
        index = index < 0
            ? (event.key === 'ArrowDown' ? 0 : links.length - 1)
            : (index + (event.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
        links[index].classList.add('active');
        links[index].scrollIntoView({block: 'nearest'});
    });

    // Reopening the dialog shows the results for whatever is still in the field.
    form.closest('.modal').addEventListener('shown.bs.modal', () => input.dispatchEvent(new Event('input')));
});

// The sidebar collapses to an icon rail on desktop; the choice is kept per browser.
document.querySelectorAll('[data-sidebar-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');

        try {
            localStorage.setItem('cv-sidebar', collapsed ? 'collapsed' : 'expanded');
        } catch (e) {
        }
    });
});

// Every single-choice .form-select becomes a searchable dropdown. The native select stays in the
// form, hidden, and holds the value: picking an option sets it and fires "change", so onchange
// handlers and the unsaved-changes guard work as before. The list is rebuilt on every open, so
// options a script adds or removes show up. Add data-no-search to keep a select native.
(function () {
    function enhance(select) {
        if (select.multiple || select.dataset.noSearch !== undefined || select.closest('.cv-select')) {
            return;
        }

        const wrapper = document.createElement('div');
        const button = document.createElement('button');
        const menu = document.createElement('div');
        const search = document.createElement('input');
        const list = document.createElement('div');

        wrapper.className = 'dropdown cv-select';
        button.type = 'button';
        button.className = select.className.replace(/\bmb-\d\b/, '') + ' cv-select-button text-start';
        button.setAttribute('data-bs-toggle', 'dropdown');
        button.setAttribute('aria-expanded', 'false');
        button.style.cssText = select.style.cssText;
        menu.className = 'dropdown-menu cv-select-menu w-100';
        search.type = 'search';
        search.className = 'form-control form-control-sm mb-2';
        search.placeholder = (document.documentElement.dataset.searchLabel || '') + '...';
        search.autocomplete = 'off';
        search.setAttribute('aria-label', document.documentElement.dataset.searchLabel || '');
        list.className = 'cv-select-list';

        if (select.labels[0] || select.getAttribute('aria-label')) {
            button.setAttribute('aria-label', select.getAttribute('aria-label') || select.labels[0].textContent.trim());
        }

        wrapper.className += select.className.match(/\bmb-\d\b/) ? ' ' + select.className.match(/\bmb-\d\b/)[0] : '';
        select.before(wrapper);
        wrapper.append(button, menu, select);
        menu.append(search, list);
        select.classList.add('visually-hidden');
        select.tabIndex = -1;

        function label() {
            const option = select.options[select.selectedIndex];
            button.textContent = option ? option.textContent.trim() || ' ' : ' ';
            button.disabled = select.disabled;
            button.classList.toggle('is-invalid', select.classList.contains('is-invalid'));
        }

        function render() {
            const term = search.value.trim().toLowerCase();
            list.replaceChildren();

            Array.from(select.children).forEach(function (node) {
                const options = node.tagName === 'OPTGROUP' ? Array.from(node.children) : [node];
                const matches = options.filter((option) => option.textContent.toLowerCase().includes(term));

                if (node.tagName === 'OPTGROUP' && matches.length) {
                    const header = document.createElement('h6');
                    header.className = 'dropdown-header';
                    header.textContent = node.label;
                    list.append(header);
                }

                matches.forEach(function (option) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'dropdown-item' + (option.selected ? ' active' : '');
                    item.textContent = option.textContent.trim() || ' ';
                    item.disabled = option.disabled;
                    item.addEventListener('click', function () {
                        // Like a native select, picking the current option changes nothing.
                        if (select.value !== option.value) {
                            select.value = option.value;
                            select.dispatchEvent(new Event('change', {bubbles: true}));
                        }

                        bootstrap.Dropdown.getOrCreateInstance(button).hide();
                        button.focus();
                    });
                    list.append(item);
                });
            });

            if (!list.children.length) {
                list.innerHTML = '<div class="dropdown-item-text text-muted small">&ndash;</div>';
            }
        }

        button.addEventListener('show.bs.dropdown', function () {
            search.value = '';
            render();
        });
        button.addEventListener('shown.bs.dropdown', function () {
            search.focus();
            list.querySelector('.active')?.scrollIntoView({block: 'nearest'});
        });
        search.addEventListener('input', render);
        search.addEventListener('keydown', function (event) {
            const first = list.querySelector('.dropdown-item:not(:disabled)');

            if (event.key === 'Enter') {
                event.preventDefault();
                first?.click();
            } else if (event.key === 'ArrowDown' && first) {
                event.preventDefault();
                first.focus();
            }
        });
        // Typing on the closed button opens the list and starts the search.
        button.addEventListener('keydown', function (event) {
            if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                bootstrap.Dropdown.getOrCreateInstance(button).show();
                search.value = event.key;
                render();
                event.preventDefault();
            }
        });
        select.addEventListener('change', label);
        // A label points at the hidden select; send its clicks to the button. Focus itself stays on the
        // select when the browser reports it invalid, so the "please fill in" bubble still shows.
        Array.from(select.labels).forEach((label) => label.addEventListener('click', function (event) {
            event.preventDefault();
            button.focus();
        }));
        select.addEventListener('invalid', () => button.classList.add('is-invalid'));
        select.addEventListener('change', () => select.validity.valid && button.classList.remove('is-invalid'));
        new MutationObserver(label).observe(select, {attributes: true, attributeFilter: ['disabled', 'class']});
        label();
    }

    document.querySelectorAll('select.form-select').forEach(enhance);

    new MutationObserver(function (mutations) {
        mutations.forEach((mutation) => mutation.addedNodes.forEach(function (node) {
            if (node.nodeType === 1) {
                (node.matches('select.form-select') ? [node] : node.querySelectorAll('select.form-select')).forEach(enhance);
            }
        }));
    }).observe(document.body, {childList: true, subtree: true});
})();
