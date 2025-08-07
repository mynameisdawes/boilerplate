import cloneDeep from "lodash/cloneDeep";

let _product_utilities = {
	fetchTieredProperty(config, _options, property) {
		let config_products = cloneDeep(config.products);
		let options = cloneDeep(_options);
		if (config_products && config_products.length > 0 && property !== undefined) {
			let rowId_found = false;

			if (config.attributes.length > 0) {
				config.attributes.forEach((attribute) => {
					if (attribute.value == 'text') {
						if (options[attribute.name] !== undefined) {
							delete options[attribute.name];
						}
					}
				});
			}

			let options_array = Object.entries(options);

			for (const product of config_products) {
				product.match_count = 0;
				if (product.attributes !== undefined && product.attributes.length > 0) {
					for (const attribute of product.attributes) {
						if (attribute.name == "rowId") {
							rowId_found = true;
						}
						for (const [option_key, option_value] of options_array) {
							if (option_key === attribute.name) {
								if (option_value != null && option_value === attribute.value) {
									product.match_count++;
								}
							}
						}
					};
				}
			}

			if (rowId_found == false) {
				delete options.rowId;
				options_array = Object.entries(options);

			}

			for (const product of config_products) {
				if (product.match_count == options_array.length) {
					if (product[property] !== undefined) {
						return product[property];
					}
					if (product.configuration !== undefined && product.configuration[property] !== undefined) {
						return product.configuration[property];
					}
				}
			}

			let highest_match_count = 0;
			for (const product of config_products) {
				if (product.match_count > highest_match_count) {
					highest_match_count = product.match_count;
				}
			}

			for (const product of config_products) {
				if (product.match_count > 0 && product.match_count == highest_match_count && product.match_count < options_array.length) {
					if (product[property] !== undefined) {
						return product[property];
					}
					if (product.configuration !== undefined && product.configuration[property] !== undefined) {
						return product.configuration[property];
					}
				}
			}
		}

		if (config[property] !== undefined) {
			return config[property];
		}
		if (config.configuration !== undefined && config.configuration[property] !== undefined) {
			return config.configuration[property];
		}

		return null;
	}
}
export default _product_utilities;