<?php if ($testmode) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_testmode; ?></div>
<?php } ?>
<?php if (!isset($currencies['ETB'])) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_currency_error; ?></div>
<?php } else{?>
<form action="<?php echo $action; ?>" method="post">
  <input type="hidden" name="Process" value="Cart" />
  <input type="hidden" name="upload" value="1" />
  <input type="hidden" name="MerchantId" value="<?php echo $business; ?>" />
  <input type="hidden" name="ExpiresInDays" value="<?php echo $expires_in_days; ?>" />
  <?php $i = 0; ?>
  <?php foreach ($products as $product) { ?>
  <input type="hidden" name="Items[<?php echo $i; ?>].ItemName" value="<?php echo $product['name']; ?>" />
  <input type="hidden" name="Items[<?php echo $i; ?>].ItemId" value="<?php echo $product['model']; ?>" />
  <input type="hidden" name="Items[<?php echo $i; ?>].UnitPrice" value="<?php echo $product['price']; ?>" />
  <input type="hidden" name="Items[<?php echo $i; ?>].Quantity" value="<?php echo $product['quantity']; ?>" />
  <?php $j = 0; ?>
  <?php foreach ($product['option'] as $option) { ?>
  <input type="hidden" name="on<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo $option['name']; ?>" />
  <input type="hidden" name="os<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo $option['value']; ?>" />
  <?php $j++; ?>
  <?php } ?>
  <?php $i++; ?>
  <?php } ?>
  <?php if ($discount_amount_cart) { ?>
    <input type="hidden" name="Discount" value="<?php echo $discount_amount_cart; ?>" />
  <?php } ?>
  <input type="hidden" name="CurrencyCode" value="<?php echo $currency_code; ?>" />
  <input type="hidden" name="invoice" value="<?php echo $invoice; ?>" />
  <input type="hidden" name="lc" value="<?php echo $lc; ?>" />
  <input type="hidden" name="rm" value="2" />
  <input type="hidden" name="no_note" value="1" />
  <input type="hidden" name="no_shipping" value="1" />
  <input type="hidden" name="charset" value="utf-8" />
  <input type="hidden" name="SuccessUrl" value="<?php echo $return; ?>" />
  <input type="hidden" name="IPNUrl" value="<?php echo $notify_url; ?>" />
  <input type="hidden" name="CancelUrl" value="<?php echo $cancel_return; ?>" />
  <input type="hidden" name="paymentaction" value="<?php echo $paymentaction; ?>" />
  <input type="hidden" name="MerchantOrderId" value="<?php echo $custom; ?>" />
  <input type="hidden" name="bn" value="OpenCart_2.0_WPS" />
  <div class="buttons">
    <div class="pull-right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
    </div>
  </div>
</form>
<?php }?>
