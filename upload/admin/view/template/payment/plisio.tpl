<?php echo $header; ?>
<div id="content">
    <style>
        .plisio-list-currencies .list-group{
          padding: 0;
          margin-bottom: .5rem;
        }
        .plisio-list-currencies .list-group-item {
          list-style: none;
          padding: 0.5em 1em;
          border: 1px dotted #ccc;
        }
        .plisio-list-currencies .list-group-item + .list-group-item{
          border-top: none;
        }
        .plisio-list-currencies .list-group-item.--check-all {
            border: none;
        }
        .plisio-list-currencies input {
            position: relative;
            top: 0.2em;
            margin: 0 .5em 0 0;
        }
        .plisio-list-currencies label {
            margin: 0;
        }
    </style>
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

<?php /**
                             <div class="form-group hidden">
                               <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_white_label; ?></label>
                               <div class="col-sm-10">
                                 <select name="plisio_white_label" id="input-white-label" class="form-control">
                                 <?php foreach ($white_label_options as $white_label_key => $white_label_value ): ?>
                                   <?php if ($white_label_key == $plisio_white_label): ?>
                                     <option value="<?php echo $white_label_key; ?>" selected="selected"><?php echo $white_label_value; ?></option>
                                    <?php else: ?>
                                     <option value="<?php echo $white_label_key; ?>"><?php echo $white_label_value; ?></option>
                                   <?php endif; ?>
                                 <?php endforeach; ?>
                                 </select>
                               </div>
                             </div>
*/ ?>


                    <tr class="plisio-list-currencies">
                        <td><?= $entry_currency; ?> <br> <i><?= $entry_currency_hint ?></i></td>
                        <td>
                            <ul class="list-group">
                                <?php if ((empty($plisio_receive_currencies) && $error_warning == '') || count($plisio_receive_currencies) === count($receive_currencies)): ?>
                                    <li class="list-group-item --check-all">
                                        <input type="checkbox" value="" id="entry_currency_0" checked="checked" />
                                        <label for="entry_currency_0"><?= $entry_currency_receive_all ?></label>
                                    </li>
                                    <?php else: ?>
                                    <li class="list-group-item --check-all">
                                        <input type="checkbox" value="" id="entry_currency_0" />
                                        <label for="entry_currency_0"><?= $entry_currency_receive_all ?></label>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <ul id="simpleList" class="list-group">
                              <?php if((empty($plisio_receive_currencies) && $error_warning == '') || count($plisio_receive_currencies) === count($receive_currencies)): ?>
                                <?php $i = 1; ?>
                                <?php foreach($receive_currencies as $currency): ?>
                                  <li class="list-group-item">
                                    <input type="checkbox" name="plisio_receive_currencies[]" value="<?= $currency['cid'] ?>" id="entry_currency_<?= $i ?>" checked="checked" />
                                    <label for="entry_currency_<?= $i ?>"><?= $currency['name'] ?> (<?= $currency['currency'] ?>)</label>
                                  </li>
                                  <?php $i++; ?>
                                <?php endforeach; ?>

                              <?php else: ?>
                                <?php $i = 1; ?>
                                <?php foreach($receive_currencies as $currency): ?>
                                  <li class="list-group-item">
                                    <?php if(is_array($plisio_receive_currencies) && in_array($currency['cid'], $plisio_receive_currencies)): ?>
                                      <input type="checkbox" name="plisio_receive_currencies[]" value="<?= $currency['cid'] ?>" id="entry_currency_<?= $i ?>" checked="checked" />
                                    <?php else: ?>
                                      <input type="checkbox" name="plisio_receive_currencies[]" value="<?= $currency['cid'] ?>" id="entry_currency_<?= $i ?>" />
                                    <?php endif; ?>
                                    <label for="entry_currency_<?= $i ?>"><?= $currency['name'] ?> (<?= $currency['currency'] ?>)</label>
                                  </li>
                                  <?php $i++; ?>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </ul>
                        </td>
                    </tr>

                    <?php /* ?>
                    <tr>
                        <td><?php echo $entry_currency; ?></td>
                        <td>
                            <select name="plisio_receive_currencies" id="input-recieve-currenies"
                                    class="form-control">
                                <option value=""><?php echo $entry_currency_receive_all; ?></option>
                                <?php foreach ($receive_currencies as $currency):?>
                                <?php if ($currency['cid'] == $plisio_receive_currencies):?>
                                <option value="<?php echo $currency['cid']; ?>"
                                        selected="selected"><?php echo $currency['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $currency['cid']; ?>"><?php echo $currency['name']; ?>
                                    (<?php echo $currency['currency']; ?>)
                                </option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php */ ?>

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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Sortable.create(simpleList, { /* options */ });

    var checkAny = document.getElementById('entry_currency_0');
    var checkSome = document.querySelectorAll('[name="plisio_receive_currencies[]"]');

    checkAny.addEventListener('click', function(event) {
      for (var i = 0; i < checkSome.length; i++) {
        checkSome[i].checked = event.target.checked;
      }
    });

    checkSome.forEach(function(element) {
      element.addEventListener('click', function() {
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
