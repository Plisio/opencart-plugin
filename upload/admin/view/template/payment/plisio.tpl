<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-worldpay" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><i class="fa fa-credit-card"></i> <?php echo $heading_title; ?></h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="panel-body">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payment"
                  class="form-horizontal">
                <div class="tab-content">
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-status"><?php echo $entry_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_status" id="input-status" class="form-control">
                                    <?php if ($plisio_status):  ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php else:  ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php endif;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-api-secret-key"><?php echo $entry_api_secret_key; ?></label>
                            <div class="col-sm-10">
                                <input type="text" name="plisio_api_secret_key"
                                       value="<?php echo $plisio_api_secret_key; ?>"
                                       placeholder="<?php echo $entry_api_secret_key; ?>" id="input-api-secret-key"
                                       class="form-control"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                            <div class="col-sm-10">
                                <input type="text" name="plisio_sort_order"
                                       value="<?php echo $plisio_sort_order; ?>"
                                       placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order"
                                       class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-order-status"><?php echo $entry_order_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_order_status_id" id="input-order-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_order_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-pending-status"><?php echo $entry_pending_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_pending_status_id" id="input-pending-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_pending_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-confirming-status"><?php echo $entry_confirming_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_confirming_status_id" id="input-pending-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_confirming_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-paid-status"><?php echo $entry_paid_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_paid_status_id" id="input-paid-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_paid_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-changeback-status"><?php echo $entry_changeback_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_changeback_status_id" id="input-changeback-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_changeback_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-expired-status"><?php echo $entry_expired_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_expired_status_id" id="input-expired-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_expired_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-invalid-status"><?php echo $entry_invalid_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_invalid_status_id" id="input-invalid-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_invalid_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-canceled-status"><?php echo $entry_canceled_status; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_canceled_status_id" id="input-canceled-status"
                                        class="form-control">
                                    <?php foreach($order_statuses as $order_status): ?>
                                    <?php if ($order_status['order_status_id']== $plisio_canceled_status_id):  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>
