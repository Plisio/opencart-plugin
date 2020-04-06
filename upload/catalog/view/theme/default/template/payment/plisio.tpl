<form action="<?php echo $action; ?>" method="post">
        <table class="form">
            <tr>
                <td align="right">
            <?php if ($currency != ''):?>
                <input type="hidden" name="currency" value="<?php echo $currency; ?>">
            <?php else: ?>
                <select name="currency">
                    <?php foreach($currencies as $currency): ?>
                        <option value="<?php echo $currency['cid']; ?>"><?php echo $currency['name']; ?> (<?php echo $currency['currency']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
                </td>
            </tr>
        </table>
        <div class="buttons">
            <div class="right">
                <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
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