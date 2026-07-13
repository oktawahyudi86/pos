/**
 * Address search via server-side Nominatim proxy.
 */

export function createPlaceSearch({ searchInput, resultsContainer, searchUrl, onSelect }) {
    let debounceTimer = null;
    let abortController = null;
    let activeIndex = -1;
    let currentResults = [];

    function closeResults() {
        resultsContainer?.classList.add('hidden');
        resultsContainer.innerHTML = '';
        currentResults = [];
        activeIndex = -1;
    }

    function renderResults(results) {
        currentResults = results;
        activeIndex = -1;

        if (!resultsContainer) {
            return;
        }

        if (!results.length) {
            resultsContainer.innerHTML = '<p class="map-search-empty">Alamat tidak ditemukan.</p>';
            resultsContainer.classList.remove('hidden');
            return;
        }

        resultsContainer.innerHTML = results.map((result, index) => `
            <button type="button" class="map-search-result" data-index="${index}">
                <span class="material-symbols-outlined map-search-result-icon">location_on</span>
                <span class="map-search-result-text">${escapeHtml(result.formatted_address)}</span>
            </button>
        `).join('');

        resultsContainer.classList.remove('hidden');

        resultsContainer.querySelectorAll('.map-search-result').forEach((button) => {
            button.addEventListener('click', () => {
                const result = currentResults[Number(button.dataset.index)];
                if (result) {
                    onSelect?.(result);
                    searchInput.value = result.formatted_address;
                    closeResults();
                }
            });
        });
    }

    async function runSearch(query) {
        abortController?.abort();
        abortController = new AbortController();

        const params = new URLSearchParams({ q: query });

        try {
            const response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: abortController.signal,
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Pencarian alamat gagal.');
            }

            renderResults(payload.results ?? []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            renderResults([]);
        }
    }

    function handleInput() {
        const query = searchInput.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 3) {
            closeResults();
            return;
        }

        debounceTimer = setTimeout(() => runSearch(query), 350);
    }

    function handleKeydown(event) {
        if (!currentResults.length || resultsContainer?.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, currentResults.length - 1);
            highlightActive();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            highlightActive();
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            const result = currentResults[activeIndex];
            onSelect?.(result);
            searchInput.value = result.formatted_address;
            closeResults();
        } else if (event.key === 'Escape') {
            closeResults();
        }
    }

    function highlightActive() {
        resultsContainer?.querySelectorAll('.map-search-result').forEach((button, index) => {
            button.classList.toggle('is-active', index === activeIndex);
        });
    }

    searchInput?.addEventListener('input', handleInput);
    searchInput?.addEventListener('keydown', handleKeydown);

    document.addEventListener('click', (event) => {
        if (!searchInput?.contains(event.target) && !resultsContainer?.contains(event.target)) {
            closeResults();
        }
    });

    return {
        close: closeResults,
        clear: () => {
            if (searchInput) {
                searchInput.value = '';
            }
            closeResults();
        },
    };
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}
