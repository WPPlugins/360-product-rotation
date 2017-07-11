/**
 * 360 Product Rotation Plugin by www.yofla.com
 *
 * Support script for woocommerce integration
 */

;(function ($, window, document, undefined) {


	/**
	 * Determine which product with 360 view was selected.
	 *
	 * Loops through variations and checks if any matches the provided selectedValues
	 *
	 * @param selectedValues Object, example:  {'color': 'red'}  ({attributeName: attributeValue})
	 * @param variations array, The product data of all variations
	 */
	var findSelectedProduct = function (selectedValues, variations) {
		var productId = undefined;

		//loop through variants
		$.each(variations, function (index, variationData) {

			//counters
			var attributesCount = 0, matches = 0;

			//handle
			var attributes = variationData['attributes'];

			//loop through variant attributes
			$.each(attributes, function (attributeName, attributeValue) {
				var isAttributeSelected = checkAttributeSelected(selectedValues, attributeName, attributeValue)
				if (isAttributeSelected) {
					matches += 1;
				}
				attributesCount += 1;
			});

			//we have found a match - a variant product those attributes match the selectedValues
			if (matches == attributesCount) {
				productId = variationData['id'] || variationData['variation_id']  //variation_id is 2.x woocommerce
			}

		});

		return productId;
	}

	/**
	 * Returns true, if attributeName of attributeValue is presented in selectedValues
	 * @param selectedValues Object, example:  {'color': 'red'}  ({attributeName: attributeValue})
	 * @param attributeName
	 * @param attributeValue
	 */
	var checkAttributeSelected = function (selectedValues, attributeName, attributeValue) {

		if (selectedValues[attributeName] && selectedValues[attributeName] == attributeValue) {
			return true;
		}
		else {
			return false;
		}
	}

	var VariationForm = function ($form) {
		this.$form = $form;
		this.$product = $form.closest('.product');
		this.variationData = $form.data('product_variations');
		//$form.on('change', '.variations select', {variationForm: this}, this.onChange);
		$form.on('show_variation', {variationForm: this}, this.onShowVariation);

	};

	/**
	 * Triggered when an attribute field changes.
	 */
	VariationForm.prototype.onShowVariation = function (event) {
		//vars
		var selectedValues = {};

		//references
		var form = event.data.variationForm;
		var variationData = form.variationData; //available variations with all data

		//loop thorough all variation groups, save selected variants
		var $selectFields = form.$form.find('select')
		$selectFields.each(function (index, selectElement) {
			var attributeName = $(selectElement).data('attribute_name')
			var selectedVariant = $(selectElement).find(":selected").val()
			selectedValues[attributeName] = selectedVariant;
		});

		var productId = findSelectedProduct(selectedValues, variationData)
		if (productId) {
			//hide all
			$('div.y360_variable_products div.y360_variant').hide();

			//show selected
			var elementId = 'y360_variant_content_' + productId;
			$('#' + elementId).show();
		}
	};

	$(function () {
		if (typeof wc_add_to_cart_variation_params !== 'undefined') {
			$('.variations_form').each(function () {
				//init form object
				new VariationForm($(this));
			});
		}
	});

})(jQuery, window, document);


