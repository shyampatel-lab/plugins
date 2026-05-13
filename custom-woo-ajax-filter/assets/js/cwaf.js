(function () {
  function serializeFilters(form) {
    const data = {};
    form.querySelectorAll('input, select').forEach((field) => {
      if (!field.name) return;
      if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
      const key = field.name.replace('[]', '');
      if (field.name.includes('[]')) {
        if (!data[key]) data[key] = [];
        data[key].push(field.value);
      } else {
        data[key] = field.value;
      }
    });
    return data;
  }

  function updateUrl(filters) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach((key) => {
      if (Array.isArray(filters[key])) params.set(key, filters[key].join(','));
      else if (filters[key] !== '') params.set(key, filters[key]);
    });
    history.pushState({ filters }, '', `${window.location.pathname}?${params.toString()}`);
  }

  async function fetchResults(wrapper, paged = 1) {
    const form = wrapper.querySelector('.cwaf-form');
    const filters = serializeFilters(form);
    updateUrl(filters);
    const body = new URLSearchParams();
    body.append('action', 'cwaf_filter_products');
    body.append('nonce', cwafData.nonce);
    body.append('paged', paged);
    body.append('relation', wrapper.dataset.relation || 'AND');
    body.append('filters', JSON.stringify(filters));
    const response = await fetch(cwafData.ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() });
    const data = await response.json();
    if (data.success) wrapper.querySelector('.cwaf-results').innerHTML = data.data.html;
  }

  document.addEventListener('change', (event) => {
    const wrapper = event.target.closest('.cwaf-wrap');
    if (wrapper) fetchResults(wrapper);
  });
  document.addEventListener('input', (event) => {
    if (event.target.matches('input[type="search"], input[type="number"]')) {
      const wrapper = event.target.closest('.cwaf-wrap');
      if (wrapper) fetchResults(wrapper);
    }
  });
  document.addEventListener('click', (event) => {
    if (event.target.matches('.cwaf-clear')) {
      const wrapper = event.target.closest('.cwaf-wrap');
      wrapper.querySelectorAll('input').forEach((input) => {
        if (input.type === 'checkbox' || input.type === 'radio') input.checked = false;
        else input.value = '';
      });
      wrapper.querySelectorAll('select').forEach((s) => (s.selectedIndex = 0));
      fetchResults(wrapper);
    }
  });
})();
