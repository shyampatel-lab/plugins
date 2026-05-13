(function () {
  function serializeFilters(form) {
    const data = {};
    form.querySelectorAll('input[type="checkbox"]:checked, select').forEach((field) => {
      const key = field.name.replace('[]', '');
      if (!data[key]) data[key] = [];
      data[key].push(field.value);
    });
    return data;
  }

  function updateUrl(filters) {
    const params = new URLSearchParams(window.location.search);
    Object.keys(filters).forEach((key) => params.set(key, filters[key].join(',')));
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
    body.append('filters', JSON.stringify(filters));

    const response = await fetch(cwafData.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });
    const data = await response.json();
    if (data.success) {
      wrapper.querySelector('.cwaf-results').innerHTML = data.data.html;
    }
  }

  document.addEventListener('change', (event) => {
    const wrapper = event.target.closest('.cwaf-wrap');
    if (wrapper) fetchResults(wrapper);
  });

  document.addEventListener('click', (event) => {
    if (event.target.matches('.cwaf-clear')) {
      const wrapper = event.target.closest('.cwaf-wrap');
      wrapper.querySelectorAll('input[type="checkbox"]').forEach((input) => (input.checked = false));
      fetchResults(wrapper);
    }
  });
})();
