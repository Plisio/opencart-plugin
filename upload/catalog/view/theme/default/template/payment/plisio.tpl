<?php if(!empty(!empty($currencies))): ?>
<form action="<?php echo $action; ?>" method="post">
        <table class="form">
            <tr>
                <td align="right">
                  <?php if (is_array($currencies) && count($currencies) === 1): ?>
                    <input type="hidden" name="currency" value="<?= $currencies[0]['cid'] ?>" class="string">
                  <?php else: ?>
                    <select name="currency" class="form-control" style="max-width: 400px">
                      <?php foreach($currencies as $currency): ?>
                      <option value="<?= $currency['cid'] ?>"><?= $currency['name'] ?>
                        (<?= $currency['currency'] ?>)</option>
                      <?php endforeach; ?>
                    </select>
                  <?php endif; ?>
                </td>
            </tr>
        </table>
        <div class="buttons">
            <div class="right">
                <input type="submit" value="<?php echo $white_label ? $button_confirm_white_label : $button_confirm; ?>" class="button" />
            </div>
        </div>
</form>
<script>
    if (!$('.quick-checkout-payment .buttons').is(':visible')){
        var inp = $('.quick-checkout-payment *[name=currency]').parent();
        inp.removeClass('col-sm-5').addClass('col-sm-12');
        inp.prepend($('<label />').attr('for', 'currency').text('Pay with: '));
    }
</script>
<?php else: ?>
<div style="color: red;">Checkout cannot be done: currency is not set. Please, contact store`s support.</div>
<?php endif; ?>
