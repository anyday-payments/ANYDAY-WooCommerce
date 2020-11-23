"use strict"; // Get the element

function clonePricetag(options) {

	var ELEM_CLASS = 'anyday-price-tag-style-wrapper';
	var elem = document.querySelector('.' + ELEM_CLASS);

	if (elem) {

	  // Create a copy of it
	  var clone = elem; // Update the ID and add a class
	  clone.querySelector('anyday-price-tag').style.display = 'block'; // Inject it into the DOM

	  if (anyday.positionSelector) {
	    var holder = document.querySelector(anyday.positionSelector);

	    if(holder) {
	    	if(options && options.deletePrev) {
		    	var prevElement = holder.previousElementSibling;
		    	if(prevElement.classList.contains(ELEM_CLASS)) {
		    		prevElement.remove();
		    	}
		    }

	    	holder.before(clone);
	    }
	  }
	}
}

function variationProductNoPriceSelectedHandler() {
	var $variationsForm = jQuery('.variations_form');

	if($variationsForm.length) {
		setTimeout(function() { variationNoPriceSelectedElementHandler()} , 500);

		$variationsForm.on('woocommerce_variation_select_change', function() {
			setTimeout(function() { variationNoPriceSelectedElementHandler()} , 500);
		});
	}
}

function variationNoPriceSelectedElementHandler() {
	var variationPriceHolderEl = document.querySelector('.woocommerce-variation');
	var noPriceSelectedEl = document.querySelector('.anyday-price-tag-style-wrapper--no-price-selected');
	var priceSelectedEl = document.querySelector('.anyday-price-tag-style-wrapper--price-selected');

	if(variationPriceHolderEl.innerHTML.length === 0 ||
		variationPriceHolderEl.style.display === 'none' ||
		variationPriceHolderEl.style.display.length === 0
	) {
		noPriceSelectedEl.style.display = 'block';
		priceSelectedEl.style.display = 'none';
	} else {
		noPriceSelectedEl.style.display = 'none';
		priceSelectedEl.style.display = 'block';
	}
}

document.addEventListener("DOMContentLoaded", function() {
  clonePricetag();
  variationProductNoPriceSelectedHandler();
});
