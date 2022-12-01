<?php if ($fail): ?>
<div class="warning"> <?php echo $fail ?> </div>
<?php endif; ?>
<form action="<?php echo $action; ?>" method="post">
    <div class="form-group">
        <div class="buttons">
            <div class="col-sm-2 pull-right">
                <input type="submit" value="<?php echo $white_label ? $button_confirm_white_label : $button_confirm; ?>" class="btn btn-primary"/>
            </div>
        </div>
    </div>
</form>
