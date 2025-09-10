async function loadSection(section) {
  const main = document.getElementById('main-content');

  try {
    const response = await fetch(`sections/${section}.html`);
    const html = await response.text();
    main.innerHTML = html;

    // Load JS after HTML injection
    if(section === 'orders') {
      const ordersModule = await import('./orders.js');
      if(ordersModule.initOrders) await ordersModule.initOrders();
    } else if(section === 'sales') {
      const salesModule = await import('./sales.js');
      if(salesModule.initSales) await salesModule.initSales();
    } else if(section === 'products') {
      const productsModule = await import('./products.js');
      if(productsModule.initPage) await productsModule.initPage();
    }else if(section === 'vouchers') {
  const vouchersModule = await import('./vouchers.js');
  if(vouchersModule.initVouchers) await vouchersModule.initVouchers();
} else if(section === 'stock') {
  const stockModule = await import('./stock.js');
  if(stockModule.initStock) await stockModule.initStock();
} else if(section === 'customers') {
    const customersModule = await import('./customers.js');
    if(customersModule.initCustomers) await customersModule.initCustomers();
}




  } catch (err) {
    console.error("Failed to load section:", err);
    main.innerHTML = "<p>Failed to load section.</p>";
  }
}
