<?php if ($fail): ?>
<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $fail ?>
    <button type="button" class="close" style="right: 0" data-dismiss="alert">&times;</button>
</div>
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
