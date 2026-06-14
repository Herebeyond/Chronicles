(function () {
    function debounce(fn, delay) {
        let timeoutId = null;

        return function debounced() {
            const args = arguments;
            const context = this;

            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            timeoutId = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    function escapeHtml(value) {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function highlightLabel(label, query) {
        const escaped = escapeHtml(label);
        const tokens = query
            .trim()
            .toLowerCase()
            .split(/\s+/)
            .filter(Boolean);

        if (!tokens.length) {
            return escaped;
        }

        let highlighted = escaped;

        tokens.forEach(function (token) {
            const escapedToken = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            if (!escapedToken) {
                return;
            }

            const regex = new RegExp('(' + escapedToken + ')', 'ig');
            highlighted = highlighted.replace(regex, '<span class="hub-match">$1</span>');
        });

        return highlighted;
    }

    function setActiveSuggestion(list, index) {
        const entries = list.querySelectorAll('.hub-suggestion');
        entries.forEach(function (entry, currentIndex) {
            entry.classList.toggle('is-active', currentIndex === index);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const page = document.querySelector('.hub-page');
        if (!page) {
            return;
        }

        const input = document.getElementById('hub-search-input');
        const list = document.getElementById('hub-suggestions');
        const endpoint = page.dataset.suggestionsEndpoint;
        const enabledPages = page.dataset.enabledPages || '';

        let latestQuery = '';
        let activeIndex = -1;

        const fetchSuggestions = debounce(function () {
            const query = (input.value || '').trim();
            latestQuery = query;
            activeIndex = -1;

            if (!query) {
                list.innerHTML = '';
                return;
            }

            const url = endpoint + '?q=' + encodeURIComponent(query) + '&scope=' + encodeURIComponent(enabledPages);

            fetch(url)
                .then(function (response) {
                    return response.json();
                })
                .then(function (payload) {
                    if (latestQuery !== query) {
                        return;
                    }

                    const suggestions = payload.suggestions || [];
                    if (!suggestions.length) {
                        list.innerHTML = '<li class="hub-empty">No matching suggestion.</li>';
                        return;
                    }

                    list.innerHTML = suggestions.map(function (suggestion) {
                        const icon = suggestion.icon || 'images/icons/icon_default.png';
                        const highlighted = highlightLabel(suggestion.label, query);
                        const cssType = 'hub-type-' + suggestion.type;
                        const cssDepth = 'hub-depth-' + suggestion.depth;

                        return [
                            '<li class="hub-suggestion ' + cssType + ' ' + cssDepth + '" data-url="' + escapeHtml(suggestion.url) + '">',
                            '<img src="' + escapeHtml(icon) + '" alt="' + escapeHtml(suggestion.typeLabel) + ' icon">',
                            '<div class="hub-suggestion-text">',
                            '<span class="hub-suggestion-label">' + highlighted + '</span>',
                            '<span class="hub-suggestion-type">(' + escapeHtml(suggestion.typeLabel) + ')</span>',
                            '</div>',
                            '</li>'
                        ].join('');
                    }).join('');
                })
                .catch(function () {
                    list.innerHTML = '<li class="hub-empty">Search is temporarily unavailable.</li>';
                });
        }, 150);

        input.addEventListener('input', fetchSuggestions);

        input.addEventListener('keydown', function (event) {
            const entries = list.querySelectorAll('.hub-suggestion');
            if (!entries.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % entries.length;
                setActiveSuggestion(list, activeIndex);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + entries.length) % entries.length;
                setActiveSuggestion(list, activeIndex);
                return;
            }

            if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                const url = entries[activeIndex].dataset.url;
                if (url) {
                    window.location.href = url;
                }
            }
        });

        list.addEventListener('click', function (event) {
            const item = event.target.closest('.hub-suggestion');
            if (!item) {
                return;
            }

            const url = item.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
    });
})();
