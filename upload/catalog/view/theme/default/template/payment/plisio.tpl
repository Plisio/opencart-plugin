<form action="<?php echo $action; ?>" method="post">
    <div class="form-group">
        <div class="buttons">
            <div class="col-sm-2 pull-right">
                <input type="submit" value="<?php echo $white_label ? $button_confirm_white_label : $button_confirm; ?>" class="btn btn-primary"/>
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
