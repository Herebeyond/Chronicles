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

        if (page.dataset.isAdmin === '1') {
            const modal = document.getElementById('hub-admin-modal');
            const form = document.getElementById('hub-card-form');
            const tokenInput = document.getElementById('hub-card-token');
            const imageInput = document.getElementById('hub-card-image-input');
            const preview = document.getElementById('hub-card-preview');
            const previewWrap = document.getElementById('hub-card-preview-wrap');
            const cropBox = document.getElementById('hub-card-crop-box');
            const cropXInput = document.getElementById('hub-crop-x');
            const cropYInput = document.getElementById('hub-crop-y');
            const cropSizeInput = document.getElementById('hub-crop-size');
            const cropSizeRange = document.getElementById('hub-crop-size-range');
            const editButtons = document.querySelectorAll('.hub-card-edit-btn');

            let crop = { x: 0, y: 0, size: 100 };
            let dragState = null;
            let currentPageKey = '';

            function clamp(value, min, max) {
                return Math.max(min, Math.min(max, value));
            }

            function toPreviewPixels() {
                const rect = preview.getBoundingClientRect();
                return {
                    width: rect.width,
                    height: rect.height
                };
            }

            function renderCropBox() {
                const box = toPreviewPixels();
                const minEdge = Math.min(box.width, box.height);

                const sizePx = (crop.size / 100) * minEdge;
                const maxOffset = 100 - crop.size;
                const xPx = maxOffset > 0 ? (crop.x / maxOffset) * (box.width - sizePx) : (box.width - sizePx) / 2;
                const yPx = maxOffset > 0 ? (crop.y / maxOffset) * (box.height - sizePx) : (box.height - sizePx) / 2;

                cropBox.style.width = sizePx + 'px';
                cropBox.style.height = sizePx + 'px';
                cropBox.style.left = xPx + 'px';
                cropBox.style.top = yPx + 'px';

                cropXInput.value = crop.x.toFixed(2);
                cropYInput.value = crop.y.toFixed(2);
                cropSizeInput.value = crop.size.toFixed(2);
                cropSizeRange.value = String(Math.round(crop.size));
            }

            function updateCropFromDrag(clientX, clientY) {
                if (!dragState) {
                    return;
                }

                const box = toPreviewPixels();
                const minEdge = Math.min(box.width, box.height);
                const sizePx = (crop.size / 100) * minEdge;

                const deltaX = clientX - dragState.startX;
                const deltaY = clientY - dragState.startY;

                const nextLeft = clamp(dragState.startLeft + deltaX, 0, box.width - sizePx);
                const nextTop = clamp(dragState.startTop + deltaY, 0, box.height - sizePx);

                const maxOffset = Math.max(0.0001, 100 - crop.size);

                crop.x = ((nextLeft / Math.max(1, (box.width - sizePx))) * maxOffset);
                crop.y = ((nextTop / Math.max(1, (box.height - sizePx))) * maxOffset);

                renderCropBox();
            }

            function openModal(pageCard, button) {
                currentPageKey = button.dataset.pageKey;
                const label = button.dataset.pageLabel || 'Page';
                const title = document.getElementById('hub-admin-modal-title');
                title.textContent = 'Edit image - ' + label;

                crop = {
                    x: clamp(parseFloat(button.dataset.cropX || '0'), 0, 100),
                    y: clamp(parseFloat(button.dataset.cropY || '0'), 0, 100),
                    size: clamp(parseFloat(button.dataset.cropSize || '100'), 10, 100)
                };

                tokenInput.value = button.dataset.csrfToken || '';

                const template = page.dataset.updateUrlTemplate;
                form.action = template.replace('__PAGE_KEY__', currentPageKey);

                if (pageCard && pageCard.classList.contains('hub-page-card--with-image')) {
                    const backgroundImage = pageCard.style.backgroundImage;
                    const match = backgroundImage.match(/url\("?([^"\)]+)"?\)/);
                    if (match && match[1]) {
                        preview.src = match[1];
                    }
                }

                if (!preview.getAttribute('src')) {
                    preview.removeAttribute('src');
                }

                modal.hidden = false;
                setTimeout(renderCropBox, 50);
            }

            function closeModal() {
                modal.hidden = true;
                imageInput.value = '';
                dragState = null;
                cropBox.classList.remove('is-dragging');
            }

            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const card = document.querySelector('.hub-page-card[data-page-key="' + button.dataset.pageKey + '"]');
                    if (!card) {
                        return;
                    }
                    openModal(card, button);
                });
            });

            modal.addEventListener('click', function (event) {
                if (event.target.closest('[data-close-modal="1"]')) {
                    closeModal();
                }
            });

            imageInput.addEventListener('change', function () {
                const file = imageInput.files && imageInput.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = String(event.target && event.target.result ? event.target.result : '');
                    preview.onload = function () {
                        crop = { x: 0, y: 0, size: 100 };
                        renderCropBox();
                    };
                };
                reader.readAsDataURL(file);
            });

            cropSizeRange.addEventListener('input', function () {
                const newSize = clamp(parseFloat(cropSizeRange.value || '100'), 10, 100);
                const centerX = crop.x + (crop.size / 2);
                const centerY = crop.y + (crop.size / 2);

                crop.size = newSize;
                const maxOffset = 100 - crop.size;
                crop.x = clamp(centerX - (crop.size / 2), 0, maxOffset);
                crop.y = clamp(centerY - (crop.size / 2), 0, maxOffset);

                renderCropBox();
            });

            cropBox.addEventListener('mousedown', function (event) {
                event.preventDefault();

                const box = toPreviewPixels();
                const cropRect = cropBox.getBoundingClientRect();
                const wrapRect = previewWrap.getBoundingClientRect();

                dragState = {
                    startX: event.clientX,
                    startY: event.clientY,
                    startLeft: cropRect.left - wrapRect.left,
                    startTop: cropRect.top - wrapRect.top,
                    boxWidth: box.width,
                    boxHeight: box.height
                };

                cropBox.classList.add('is-dragging');
            });

            window.addEventListener('mousemove', function (event) {
                if (!dragState) {
                    return;
                }

                updateCropFromDrag(event.clientX, event.clientY);
            });

            window.addEventListener('mouseup', function () {
                if (!dragState) {
                    return;
                }

                dragState = null;
                cropBox.classList.remove('is-dragging');
            });

            window.addEventListener('resize', function () {
                if (!modal.hidden) {
                    renderCropBox();
                }
            });

            form.addEventListener('submit', function () {
                renderCropBox();
            });
        }
    });
})();
