/**
 * Calculate the average buy price from a list of purchases
 * @param purchases - Array of purchases with qty and price
 * @returns Average buy price
 */
export function avgBuyPrice(purchases: { qty: number; price: number }[]): number {
  const total = purchases.reduce((s, p) => s + p.qty * p.price, 0);
  const qty = purchases.reduce((s, p) => s + p.qty, 0);
  return qty ? total / qty : 0;
}

/**
 * Calculate unrealized profit/loss
 * @param purchases - Array of purchases with qty and price
 * @param currentPrice - Current market price
 * @returns Unrealized P/L amount
 */
export function unrealizedPL(
  purchases: { qty: number; price: number }[],
  currentPrice: number
): number {
  const qty = purchases.reduce((s, p) => s + p.qty, 0);
  const avg = avgBuyPrice(purchases);
  return currentPrice * qty - avg * qty;
}
