<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <style>
        .plisio-list-currencies .list-group-item {
            display: flex;
            align-items: center;
        }

        .plisio-list-currencies .list-group-item.--check-all {
            border: none;
        }

        .plisio-list-currencies input {
            margin: 0 .5em 0 0;
        }

        .plisio-list-currencies label {
            margin: 0;
        }
    </style>
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
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_settings; ?></a></li>
                    <li><a href="#tab-order-status" data-toggle="tab"><?php echo $tab_order_status; ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-general">
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


                        <div class="form-group hidden">
                            <label class="col-sm-2 control-label"
                                   for="input-order-status"><?php echo $entry_white_label; ?></label>
                            <div class="col-sm-10">
                                <select name="plisio_white_label" id="input-white-label" class="form-control">
                                    <?php foreach ($white_label_options as $white_label_key => $white_label_value): ?>
                                    <?php if ($white_label_key == $plisio_white_label):  ?>
                                    <option value="<?php echo $white_label_key; ?>"
                                            selected="selected"><?php echo $white_label_value; ?></option>
                                    <?php else:  ?>
                                    <option value="<?php echo $white_label_key; ?>"><?php echo $white_label_value; ?></option>
                                    <?php endif;  ?>
                                    <?php endforeach;  ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group plisio-list-currencies">
                            <label class="col-sm-2 control-label"
                                   for="input-order-status"><?php echo $entry_currency; ?></label>
                            <div class="col-sm-10">

                                <ul class="list-group">
                                    <?php if ((empty($plisio_receive_currencies) && $error_warning == '') || (count($plisio_receive_currencies) == count($receive_currencies))): ?>
                                    <li class="list-group-item --check-all">
                                        <input type="checkbox" value="" id="entry_currency_0" checked="checked"/>
                                        <label for="entry_currency_0"><?php echo $entry_currency_receive_all; ?></label>
                                    </li>
                                    <?php else:  ?>
                                    <li class="list-group-item --check-all">
                                        <input type="checkbox" value="" id="entry_currency_0"/>
                                        <label for="entry_currency_0"><?php echo $entry_currency_receive_all; ?></label>
                                    </li>
                                    <?php endif;  ?>
                                </ul>

                                <ul id="simpleList" class="list-group">
                                    <?php if ((empty($plisio_receive_currencies) && $error_warning == '') || (count($plisio_receive_currencies) == count($receive_currencies))): ?>
                                    <?php foreach($receive_currencies as $currency): ?>
                                    <li class="list-group-item">
                                        <input type="checkbox" name="plisio_receive_currencies[]"
                                               value="<?php echo $currency['cid']; ?>"
                                               id="entry_currency_<?php echo $currency['cid']; ?>" checked="checked"/>
                                        <label for="entry_currency_<?php echo $currency['cid']; ?>"><?php echo $currency['name']; ?>
                                            (<?php echo $currency['currency']; ?>)</label>
                                    </li>
                                    <?php endforeach;  ?>
                                    <?php else:  ?>
                                    <?php foreach($receive_currencies as $currency): ?>
                                    <li class="list-group-item">
                                        <?php if (in_array($currency['cid'], $plisio_receive_currencies)):  ?>
                                        <input type="checkbox" name="plisio_receive_currencies[]"
                                               value="<?php echo $currency['cid']; ?>"
                                               id="entry_currency_<?php echo $currency['cid']; ?>" checked="checked"/>
                                        <?php else:  ?>
                                        <input type="checkbox" name="plisio_receive_currencies[]"
                                               value="<?php echo $currency['cid']; ?>"
                                               id="entry_currency_<?php echo $currency['cid']; ?>"/>
                                        <?php endif;  ?>
                                        <label for="entry_currency_<?php echo $currency['cid']; ?>"><?php echo $currency['name']; ?>
                                            (<?php echo $currency['currency']; ?>)</label>
                                    </li>
                                    <?php endforeach;  ?>
                                    <?php endif;  ?>
                                    <?php if (isset($error_plisio_receive_currencies)) { ?>
                                    <div class="text-danger"><?php echo $error_plisio_receive_currencies; ?></div>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab-order-status">
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
                </div>

            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Sortable.create(simpleList, { /* options */});

        var checkAny = document.getElementById('entry_currency_0');
        var checkSome = document.querySelectorAll('[name="plisio_receive_currencies[]"]');

        checkAny.addEventListener('click', function (event) {
            for (var i = 0; i < checkSome.length; i++) {
                checkSome[i].checked = event.target.checked;
            }
        });

        checkSome.forEach(function (element) {
            element.addEventListener('click', function () {
                var values = 0;
                for (var i = 0; i < checkSome.length; i++) {
                    if (checkSome[i].checked) {
                        values++;
                    }
                }
                checkAny.checked = (values === checkSome.length);
            });
        });

    });
</script>
<?php echo $footer; ?>
