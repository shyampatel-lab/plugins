(function () {
  function serializeFilters(form) {
    const data = {};
    form.querySelectorAll('input, select').forEach((field) => {
      if (!field.name) return;
      if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
      if (field.type === 'range' && field.dataset.source) {
        data[`${field.dataset.source}_${field.dataset.bound}`] = field.value;
        return;
      }
      const key = field.name.replace('[]', '');
      if (field.name.includes('[]')) {
        if (!data[key]) data[key] = [];
        data[key].push(field.value);
      } else data[key] = field.value;
    });
    return data;
  }
  function updateUrl(filters) { const p = new URLSearchParams(); Object.keys(filters).forEach((k)=>p.set(k, Array.isArray(filters[k])?filters[k].join(','):filters[k])); history.pushState({filters}, '', `${window.location.pathname}?${p.toString()}`); }
  async function fetchResults(wrapper) {
    const filters = serializeFilters(wrapper.querySelector('.cwaf-form')); updateUrl(filters);
    const body = new URLSearchParams({ action:'cwaf_filter_products', nonce:cwafData.nonce, paged:'1', relation: wrapper.dataset.relation || 'AND', filters: JSON.stringify(filters) });
    const res = await fetch(cwafData.ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() });
    const data = await res.json(); if(data.success) wrapper.querySelector('.cwaf-results').innerHTML = data.data.html;
  }
  document.addEventListener('change', (e)=>{ const w=e.target.closest('.cwaf-wrap'); if(w) fetchResults(w); });
  document.addEventListener('input', (e)=>{ if(e.target.matches('input[type="search"],input[type="number"],input[type="range"]')) { const w=e.target.closest('.cwaf-wrap'); if(w) fetchResults(w);} });
  document.addEventListener('click', (e)=>{ if(e.target.matches('.cwaf-clear')){ const w=e.target.closest('.cwaf-wrap'); w.querySelectorAll('input').forEach((i)=>{ if(['checkbox','radio'].includes(i.type)) i.checked=false; else i.value=i.min||'';}); w.querySelectorAll('select').forEach((s)=>s.selectedIndex=0); fetchResults(w);} });
})();
