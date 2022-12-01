<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a
                href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/payment.png" alt=""/> <?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a
                        href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td><span class="required">*</span> <?php echo $entry_status; ?></td>
                        <td>
                            <select name="plisio_status" id="input-status" class="form-control">
                                <?php if ($plisio_status): ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php else: ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><span class="required">*</span><?php echo $entry_api_secret_key; ?></td>
                        <td>
                            <input type="text" name="plisio_api_secret_key"
                                   value="<?php echo $plisio_api_secret_key; ?>"
                                   placeholder="<?php echo $entry_api_secret_key; ?>" id="input-api-secret-key"
                                   class="form-control"/>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_sort_order; ?></td>
                        <td>
                            <input type="text" name="plisio_sort_order"
                                   value="<?php echo $plisio_sort_order; ?>"
                                   placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order"
                                   class="form-control"/>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_order_status; ?></td>
                        <td>
                            <select name="plisio_order_status_id" id="input-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status):?>
                                <?php if ($order_status['order_status_id'] == $plisio_order_status_id):?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr

                    <tr>
                        <td><?php echo $entry_pending_status; ?></td>
                        <td>
                            <select name="plisio_pending_status_id" id="input-pending-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status):?>
                                <?php if ($order_status['order_status_id'] == $plisio_pending_status_id):?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr

                    <tr>
                        <td><?php echo $entry_confirming_status; ?></td>
                        <td>
                            <select name="plisio_confirming_status_id" id="input-pending-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status):?>
                                <?php if ($order_status['order_status_id'] == $plisio_confirming_status_id):?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_paid_status; ?></td>
                        <td>
                            <select name="plisio_paid_status_id" id="input-paid-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status):?>
                                <?php if ($order_status['order_status_id'] == $plisio_paid_status_id):?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_changeback_status; ?></td>
                        <td>
                            <select name="plisio_changeback_status_id" id="input-changeback-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status): ?>
                                <?php if ($order_status['order_status_id'] == $plisio_changeback_status_id): ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_expired_status; ?></td>
                        <td class="col-sm-10">
                            <select name="plisio_expired_status_id" id="input-expired-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status):?>
                                <?php if ($order_status['order_status_id'] == $plisio_expired_status_id): ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_invalid_status; ?></td>
                        <td>
                            <select name="plisio_invalid_status_id" id="input-invalid-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status): ?>
                                <?php if ( $order_status['order_status_id'] == $plisio_invalid_status_id): ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $entry_canceled_status; ?></td>
                        <td>
                            <select name="plisio_canceled_status_id" id="input-canceled-status"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status): ?>
                                <?php if ( $order_status['order_status_id'] == $plisio_canceled_status_id): ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>
