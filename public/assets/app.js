document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); });
});
const company = document.querySelector('[name="empresa"]');
const heritage = document.querySelector('[name="patrimonio"]');
const type = document.querySelector('[name="tipo"]');
const fields = document.getElementById('cell-fields');
function updateAssetFields() {
  if (company && heritage) {
    heritage.disabled = company.value === 'Vivo';
    if (heritage.disabled) heritage.value = '';
  }
  if (type && fields) {
    fields.hidden = type.value.trim().toUpperCase() !== 'CELULAR';
    fields.querySelectorAll('input').forEach(input => input.disabled = fields.hidden);
  }
}
company?.addEventListener('change', updateAssetFields);
type?.addEventListener('input', updateAssetFields);
updateAssetFields();
document.querySelectorAll('input[type="number"]').forEach(input => { input.min = '1'; input.max = '2147483647'; input.step = '1'; });
