(() => {
    const findButton = document.getElementById('find-nearby');
    const form = document.getElementById('manual-search-form');
    const cityInput = document.getElementById('city');
    const emptyState = document.getElementById('directory-empty');
    const loadingState = document.getElementById('directory-loading');
    const resultsState = document.getElementById('directory-results');
    const alertBox = document.getElementById('directory-alert');
    const locationStatus = document.getElementById('location-status');
    const centerList = document.getElementById('center-list');
    const resultCount = document.getElementById('result-count');
    const endpoint = '/api/help-centers/nearby';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);

    const setState = (state) => {
        emptyState.classList.toggle('d-none', state !== 'empty');
        loadingState.classList.toggle('d-none', state !== 'loading');
        resultsState.classList.toggle('d-none', state !== 'results');
    };

    const showAlert = (message) => {
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    };

    const formatHours = (hours) => {
        if (!hours || typeof hours !== 'object') return '';
        return Object.entries(hours).map(([day, value]) => `${escapeHtml(day)}: ${escapeHtml(value)}`).join(' | ');
    };

    const renderCenters = (centers) => {
        centerList.innerHTML = centers.map((center) => {
            const hotlines = (center.hotlines || []).map((hotline) => `
                <div class="mb-2">
                    <a class="hotline-link" href="tel:${escapeHtml(hotline.phone_number)}"><i class="fas fa-phone me-2"></i>${escapeHtml(hotline.phone_number)}</a>
                    ${hotline.name ? `<div class="small text-secondary">${escapeHtml(hotline.name)}</div>` : ''}
                </div>`).join('');
            const address = [center.address, center.city, center.state, center.zip_code].filter(Boolean).map(escapeHtml).join(', ');
            const hours = formatHours(center.working_hours);

            const distance = center.distance_km === null ? 'Distance unavailable' : `${escapeHtml(center.distance_km)} km`;

            return `<div class="col-md-6">
                <article class="center-card p-4">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div><div class="center-type mb-2">${escapeHtml(center.type_label)}</div><h3 class="h5 mb-0">${escapeHtml(center.name)}</h3></div>
                        <span class="center-distance">${distance}</span>
                    </div>
                    ${address ? `<p class="mb-2 text-secondary"><i class="fas fa-location-dot me-2"></i>${address}</p>` : ''}
                    ${hours ? `<p class="working-hours mb-3"><i class="far fa-clock me-2"></i>${hours}</p>` : ''}
                    ${hotlines ? `<div class="border-top pt-3"><p class="small fw-bold mb-2">Active hotline${center.hotlines.length === 1 ? '' : 's'}</p>${hotlines}</div>` : '<p class="small text-secondary mb-0">No hotline listed.</p>'}
                </article>
            </div>`;
        }).join('');
    };

    const lookup = async (city = '') => {
        setState('loading');
        alertBox.classList.add('d-none');
        locationStatus.classList.add('d-none');
        const query = city ? `?city=${encodeURIComponent(city)}` : '';

        try {
            const response = await fetch(`${endpoint}${query}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok || payload.status !== 'success') throw new Error(payload.message || 'Search unavailable.');

            const { location, centers } = payload.data;
            locationStatus.innerHTML = `<i class="fas fa-location-crosshairs me-2"></i><strong>${escapeHtml(location.city)}${location.country ? `, ${escapeHtml(location.country)}` : ''}</strong> <span class="small ms-2">${location.approximate ? 'Approximate network location' : 'Manual city search'}</span>`;
            locationStatus.classList.remove('d-none');

            if (!centers.length) {
                setState('empty');
                showAlert(`No active help centers were found in ${location.city}. Try another nearby city.`);
                return;
            }

            renderCenters(centers);
            resultCount.textContent = `${centers.length} option${centers.length === 1 ? '' : 's'} found`;
            setState('results');
        } catch (error) {
            setState('empty');
            showAlert(error.message || 'We could not complete the search. Try searching by city instead.');
        }
    };

    findButton.addEventListener('click', () => lookup());
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const city = cityInput.value.trim();
        if (city) lookup(city);
    });
})();
