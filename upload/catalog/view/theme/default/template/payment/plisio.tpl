<form action="<?php echo $action; ?>" method="post">
    <div class="form-group">
        <div class="col-sm-5 col-sm-offset-5">
            <?php if (is_array($currencies) && count($currencies) == 1): ?>
                <input type="hidden" name="currency" value="<?php echo $currencies[0]['cid']; ?>" class="string">
            <?php else: ?>
                <select name="currency" class="form-control" style="max-width: 400px">
                    <?php foreach($currencies as $currency): ?>
                        <option value="<?php echo $currency['cid']; ?>"><?php echo $currency['name']; ?> (<?php echo $currency['currency']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <div class="buttons">
            <div class="col-sm-2 pull-right">
                <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary"/>
            </div>
        </div>
    </div>
</form>
<script>
    if (!$('.quick-checkout-payment .buttons').is(':visible')) {
        var inp = $('.quick-checkout-payment *[name=currency]').parent();
        inp.removeClass('col-sm-5').addClass('col-sm-12');
        inp.prepend($('<label />').attr('for', 'currency').text('<?php echo $pay_with_text; ?>'));
    }
</script>