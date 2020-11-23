"use strict";

jQuery(document).ready(function ($) {

  $('.anyday-payment-action').on('click', function(e) {
    var action = $(this).data('anydayAction'),
        data = {
          action: action,
          orderId: $(this).attr('data-order-id'),
        };

    if(action === 'adm_capture_payment') {
      var amount = prompt(anyday.capturePrompt);

      if(amount === null) return;

      amount = parseFloat(amount.replace(',' , '.'));

      if(isNaN(amount)) {
        alert(anyday.capturePromptValidation);
        return;
      }

      data.amount = amount;
    } else if (action === 'adm_cancel_payment') {
      var shouldCancel = confirm(anyday.cancelConfirmation);

      if(!shouldCancel) return;
    } else if (action === 'adm_refund_payment') {
      var amount = prompt(anyday.refundConfirmation);

      if(amount === null) return;

      amount = parseFloat(amount.replace(',' , '.'));

      if(isNaN(amount)) {
        alert(anyday.capturePromptValidation);
        return;
      }

      data.amount = amount;
    }

    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: anyday.ajaxUrl,
      data,
      success: function(response){
        if (response.success) {
          if(!alert(response.success)){window.location.reload(true);}
        } else if (response.error) {
          if(!alert(response.error)){window.location.reload(true);}
        }
      }
    });
  });
});