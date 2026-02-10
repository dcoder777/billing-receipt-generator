(() => {
  const itemsBody = document.getElementById('itemsBody');
  const addItemBtn = document.getElementById('addItemBtn');
  const taxInput = document.getElementById('tax_percent');
  const subtotalNode = document.getElementById('subtotal_text');
  const taxNode = document.getElementById('tax_text');
  const totalNode = document.getElementById('grand_total_text');

  const recalc = () => {
    const rows = document.querySelectorAll('.item-row');
    let subtotal = 0;

    rows.forEach((row) => {
      const qty = parseFloat(row.querySelector('.qty').value) || 0;
      const price = parseFloat(row.querySelector('.price').value) || 0;
      const discount = parseFloat(row.querySelector('.discount').value) || 0;
      const line = qty * price * (1 - Math.min(discount, 100) / 100);
      subtotal += line;
      row.querySelector('.line-total').textContent = line.toFixed(2);
    });

    const taxPercent = parseFloat(taxInput.value) || 0;
    const tax = subtotal * taxPercent / 100;
    const grand = subtotal + tax;

    subtotalNode.textContent = subtotal.toFixed(2);
    taxNode.textContent = tax.toFixed(2);
    totalNode.textContent = grand.toFixed(2);
  };

  const attachRowEvents = (row) => {
    row.querySelectorAll('input').forEach((input) => {
      input.addEventListener('input', recalc);
    });
    row.querySelector('.remove-row').addEventListener('click', () => {
      row.remove();
      recalc();
    });
  };

  const createRow = () => {
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
      <td><input name="item_name[]" required placeholder="Item name"></td>
      <td><input class="qty" name="quantity[]" type="number" inputmode="decimal" min="0" step="0.01" value="1" required></td>
      <td><input class="price" name="price[]" type="number" inputmode="decimal" min="0" step="0.01" value="0" required></td>
      <td><input class="discount" name="discount[]" type="number" inputmode="decimal" min="0" max="100" step="0.01" value="0"></td>
      <td class="line-total">0.00</td>
      <td><button type="button" class="secondary remove-row">Remove</button></td>
    `;
    itemsBody.appendChild(tr);
    attachRowEvents(tr);
    recalc();
  };

  if (addItemBtn && itemsBody) {
    addItemBtn.addEventListener('click', createRow);
  }

  document.querySelectorAll('.item-row').forEach(attachRowEvents);
  taxInput?.addEventListener('input', recalc);
  recalc();
})();
