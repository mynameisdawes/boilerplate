let _pricing = {
	pricing: {
		mmSquared: 1000,
		firstPrintCost: 0.1,
		secondPrintCost: 7/120,
		minCostPerGarment: 50/3
	},
	discounts: [
		{
			minQuantity: 3,
			maxQuantity: 5,
			percentage: 10,
		},
		{
			minQuantity: 6,
			maxQuantity: 10,
			percentage: 15,
		},
		{
			minQuantity: 11,
			maxQuantity: Infinity,
			percentage: 17,
		}
	],
	initialiseDiscounts() {
		this.discounts.forEach(disc => {
			disc.discount = disc.percentage / 100;
			disc.multiplier = 1 - disc.discount;
			disc.range = `${disc.minQuantity}`;
			disc.range += disc.maxQuantity !== Infinity ?
			`-${disc.maxQuantity}` :
			`+`;
		});
	},
}

export default _pricing;