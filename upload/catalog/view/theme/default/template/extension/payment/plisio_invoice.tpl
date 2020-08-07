<style>
    .invoice__amount {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .invoice .invoice__progress {
        position: relative;
        height: 2rem;
        background: #229ac8;
    }

    .invoice__progressBar {
        will-change: width;
        transition: width 1s linear !important;
    }

    .invoice__progressHint {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        text-align: center;
        text-shadow: 0 0 1px #000;
        color: #fff;
    }

    .invoice__qr {
        margin: 2rem auto;
    }

    .invoice__btn_copy {
        cursor: pointer;
    }

    .invoice .input-group {
        width: 100%;
    }

    .invoice .input-group-addon {
        width: 55px;
    }

    .invoice .form-control {
        width: 100%;
        text-align: center;
    }

    .invoice a > .glyphicon {
        margin-right: .5em;
    }

    .invoice__result .glyphicon:not(.glyphicon-share) {
        display: block;
        margin: 0 auto 2rem;
        font-size: 80px;
        /*color: #23a1d1; !* bootstrap primary-color *!*/
    }

    @keyframes rotate {
        100% {
            transform: rotate(360deg);
        }
    }

    @-webkit-keyframes rotate {
        100% {
            transform: rotate(360deg);
        }
    }

    .invoice .glyphicon-refresh {
        animation: rotate infinite 1s linear;
        transform-origin: 50% 50%;
    }
</style>
<?php echo $header; ?>

<div class="container">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
</div>

<div class="invoice__wrapper container">
    <div class="row">
        <div
                class="invoice col-md-offset-3 col-md-6 col-sm-12"
                data-invoice-id="<?php echo $plisio_invoice_id; ?>"
                data-invoice-amount="<?php echo $amount; ?>"
                data-invoice-currency="<?php echo $currency; ?>"
        >
            <?php if (in_array($status, ['new', 'pending'])): ?>
            <div class="invoice__progress progress">
                <div
                        class="invoice__progressBar progress-bar"
                        role="progressbar"
                        aria-valuenow="0"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        style="width: 0"
                        data-expire-utc="<?php echo $expire_utc; ?>"
                >
                    <span class="sr-only">0% Complete</span>
                </div>
                <span class="invoice__progressHint"></span>
            </div>
            <?php endif; ?>

            <div class="invoice__content">
                <?php if ($status == 'new' || ($status == 'pending' && $pending_amount > 0)):  ?>
                <div class="row">
                    <div class="col-xs-4">
                        <h4><small>Order #</small><?php echo $order_id; ?></h4>
                    </div>
                    <div class="col-xs-8 text-right">
                        <div class="invoice__amount">
                            <div class="invoice__amountSum">
                                <strong><?php echo $amount; ?> <?php echo $currency; ?></strong> <br>
                                <?php echo number_format($amount/$source_rate, 2); ?> <?php echo $source_currency; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <img
                            class="invoice__qr"
                            src="<?php echo $qr_code; ?>"
                            alt="invoice qr code"
                            width="160"
                            height="160"
                    >
                </div>
                <p class="invoice__hint text-center text-large">Send the indicated amount to the address below:</p>
                <div class="form-group">
                    <div class="input-group">
              <span class="input-group-addon">
                <img
                        class="invoice__psysImg"
                        src="<?php echo 'https://plisio.net/img/psys-icon/' . $currency . '.svg'; ?>"
                        alt="<?php echo $currency; ?>"
                        width="16"
                        height="16"
                >
              </span>
                        <input
                                type="text"
                                class="form-control"
                                value="<?php echo $wallet_hash; ?>"
                                readonly
                                onclick="copyInvoiceValue(this)" data-toggle="tooltip" title="Hash copied" data-trigger="click"
                        >
                        <span class="input-group-addon btn btn-primary" onclick="copyInvoiceValue(this)" data-toggle="tooltip" title="Hash copied" data-trigger="click">
                <i class="glyphicon glyphicon-duplicate invoice__btn_copy"></i>
              </span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo $currency; ?></span>
                        <input
                                type="text"
                                class="form-control invoice__pendingAmount"
                                value="<?php echo $pending_amount; ?>"
                                readonly
                                onclick="copyInvoiceValue(this)" data-toggle="tooltip" title="Amount copied" data-trigger="click"
                        >
                        <span class="input-group-addon btn btn-primary" onclick="copyInvoiceValue(this)" data-toggle="tooltip" title="Amount copied" data-trigger="click">
                <i class="glyphicon glyphicon-duplicate invoice__btn_copy"></i>
              </span>
                    </div>
                </div>
                <?php elseif ($status == 'pending' && $pending_amount <= 0): ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-refresh text-primary"></i>
                    <?php $stringConfirm = $expected_confirmations > 1 ? 'confirmations' :  'confirmation' ?>
                    <h3>Waiting for <?php echo $expected_confirmations-confirmations; ?> of <?php echo $expected_confirmations; ?> <?php echo $stringConfirm; ?></h3>
                    <p>Please, wait until network confirms your payment. It usually takes 15-60 minutes.</p>
                    <a href="<?php echo $txUrl; ?>" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>
                </div>
                <?php elseif (in_array($status, ['finish', 'completed'])): ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-ok text-primary"></i>
                    <h3>Payment complete</h3>
                    <a href="<?php echo $txUrl; ?>" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>
                </div>
                <?php elseif ($status == 'mismatch'): ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-ok text-primary"></i>
                    <h3>The order has been overpaid</h3>
                    <p>You have payed <?php echo $amount + $pending_amount * (-1); ?> <?php echo $currency; ?>,
                        it is more than required sum. In case of inconvenience, please, contact store`s support.</p>
                    <a href="<?php echo $txUrl; ?>" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>
                </div>
                <?php elseif (in_array($status, ['expired', 'cancelled'])): ?>
                <?php if ($pending_amount < $amount): ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-repeat text-primary"></i>
                    <h3>The order has not been fully paid</h3>
                    <p>We have received  <?php echo $amount - $pending_amount; ?> <?php echo $currency; ?> of <?php echo $amount; ?>  <?php echo $currency; ?> required.
                        To get your payment back, please, contact store`s support.</p>
                </div>
                <?php else: ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-repeat text-primary"></i>
                    <h3>The order has expired</h3>
                    <p>Please, <a href="/" title="go back">go back</a> and create a new one.</p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="invoice__result text-center">
                    <i class="glyphicon glyphicon-warning-sign text-danger"></i>
                    <h3>Ooops...</h3>
                    <p>Something went wrong with this operation. Please, contact store`s support, so we could figure this out.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (in_array($status, ['new', 'pending'])): ?>
    <script async defer>
        function Timer(options) {
            if (Object.keys(options).length > 0) {
                if (options.elSelector) {
                    this.el = document.querySelector(options.elSelector);
                } else {
                    throw new ReferenceError("Invalid element selector passed");
                }
                if (options.countDownTimestamp) {
                    this.countDownTimestamp = options.countDownTimestamp;
                } else {
                    throw new ReferenceError("Invalid timestamp");
                }
                if (options.callback) {
                    this.callback = options.callback;
                }
                this._dateToRender = Object.create(null);
            } else {
                throw new ReferenceError("Invalid input data");
            }

        }

        Timer.prototype.calc = function () {
            this._now = new Date().getTime();
            this._distance = this.countDownTimestamp - this._now;
            // this._dateToRender.days = Math.floor(this._distance / (1000 * 60 * 60 * 24));
            this._dateToRender.hours = Math.floor((this._distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this._dateToRender.minutes = Math.floor((this._distance % (1000 * 60 * 60)) / (1000 * 60));
            this._dateToRender.seconds = Math.floor((this._distance % (1000 * 60)) / 1000);
            this._progress = 100 - (this._distance / this._distanceAll) * 100;
            this._progress = Math.round(this._progress * 100) / 100;
        };

        Timer.prototype.render = function () {
            this.el.style.width = this._progress + '%';
            this.el.setAttribute('aria-valuenow', this._progress);
            this.el.querySelector('.sr-only').textContent = this._progress + '% Complete';
            var stringToRender = '';
            if (this._distance <= 0) {
                var self = this;
                setTimeout(function () {
                    stringToRender += 'This order has been expired.';
                    self.el.nextElementSibling.textContent = stringToRender;
                }, 1000);
            } else {
                for (var key in this._dateToRender) {
                    this._dateToRender[key] = this._dateToRender[key] >= 10 ? this._dateToRender[key] : '0' + this._dateToRender[key]
                }
                stringToRender = Object.values(this._dateToRender).join(':');
                this.el.nextElementSibling.textContent = stringToRender;
            }
        };

        Timer.prototype.fin = function () {
            clearTimeout(this._timerId);
            if (this.callback) {
                if (typeof this.callback === 'function') {
                    this.callback();
                } else {
                    throw new ReferenceError("Callback-param must be function.");
                }
            }
            delete this;
        };

        Timer.prototype.run = function () {
            this.calc();
            this._distanceAll = this._distance;
            this.render();
            if (this._distance <= 0) {
                this.fin();
            }
            var self = this;
            this._timerId = setTimeout(function runTimer() {
                self.calc();
                self.render();
                if (self._distance < 0) {
                    self.fin();
                } else {
                    self._timerId = setTimeout(runTimer, 1000);
                }
            }, 1000);
        };


        document.addEventListener('DOMContentLoaded', function () {
            var elInvoice = document.querySelector('.invoice');
            var elProgressBar = elInvoice.querySelector('.invoice__progressBar');
            var expireUTC = new Date(elProgressBar.dataset.expireUtc);
            var countDownTimestamp = expireUTC.getTime() - expireUTC.getTimezoneOffset() * 60 * 1000;

            // var now = new Date();
            // var diff = countDownDate - now.getTime();


            (function () {
                function InvoiceTimer(el, options) {
                    Timer.apply(this, arguments);
                }
                InvoiceTimer.prototype = Object.create(Timer.prototype);
                InvoiceTimer.prototype.constructor = InvoiceTimer;
                window.invoiceTimer = new InvoiceTimer(
                    {
                        elSelector: '.invoice__progressBar',
                        countDownTimestamp: countDownTimestamp,
                        callback: function () {
                            // console.info('Callback-function called after invoice timer finishes.')
                        },
                    }
                );
                window.invoiceTimer.run();
            })();


            function getTxUrl (tx_urls) {
                var txUrl = '';
                if (tx_urls) {
                    try {
                        txUrl = JSON.parse(tx_urls);
                        if (txUrl) {
                            txUrl = typeof txUrl === 'string' ? txUrl : txUrl[txUrl.length-1];
                        }
                    } catch (error) {
                        console.error('Failed to parse tx_urls: ', error);
                    }
                }
                return txUrl;
            }

            function finInvoiceChecking () {
                clearTimeout(window.checkInvoiceInterval);
                window.invoiceTimer.fin();
            }

            if (!window.checkInvoiceInterval){
                window.checkInvoiceInterval = setInterval(function () {
                    $.ajax({
                        type: 'GET',
                        url:'/index.php?route=extension/payment/plisio/invoice',
                        dataType: 'json',
                        data: {
                            invoice: document.querySelector('.invoice').dataset.invoiceId,
                        },
                        error: function (error) {
                            console.error('check invoice interval error: ', error);
                            finInvoiceChecking();
                        },
                        success: function (response) {
                            if (!['new', 'pending'].includes(response.status)) {
                                finInvoiceChecking();
                            }
                            if (['new'].includes(response.status)
                                || ['pending'].includes(response.status) && response.pending_amount > 0)
                            {
                                if (response.pending_amount < response.amount) {
                                    elInvoice.querySelector('.invoice__pendingAmount').value = response.pending_amount;
                                    elInvoice.querySelector('.invoice__qr').setAttribute('src', response.qr_code);
                                }
                                return;
                            }
                            var resultContent = '';
                            var txUrl = getTxUrl(response.tx_urls);
                            if (['pending'].includes(response.status)) {
                                var confirmString = response.expected_confirmations > 1 ? 'confirmations' : 'confirmation';
                                resultContent += '<div class="invoice__result text-center">' +
                                    '<i class="glyphicon glyphicon-refresh text-primary"></i>' +
                                    '<h3>Waiting for ' + (Number(response.expected_confirmations)-Number(response.confirmations)) + ' of ' + response.expected_confirmations + ' ' + confirmString + '</h3>' +
                                    '<p>Please, wait until network confirms your payment. It usually takes 15-60 minutes.</p>' +
                                    '<a href="'+ txUrl + '" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>' +
                                    '</div>';
                            } else if (['finish', 'completed'].includes(response.status)) {
                                resultContent += '<div class="invoice__result text-center">' +
                                    '<i class="glyphicon glyphicon-ok text-primary"></i>' +
                                    '<h3>Payment complete</h3>' +
                                    '<a href="'+ txUrl + '" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>' +
                                    '</div>';
                            } else if (['mismatch'].includes(response.status)) {
                                resultContent += '<div class="invoice__result text-center">' +
                                    '<i class="glyphicon glyphicon-ok text-primary"></i>' +
                                    '<h3>The order has been overpaid</h3>' +
                                    '<p>You have payed ' + (Math.abs(response.pending_amount) + Number(response.amount)).toFixed(8) + ' ' + response.currency + ', ' +
                                    'it is more than required sum. In case of inconvenience, please, contact store`s support.</p>' +
                                    '<a href="'+ txUrl + '" title="Check my transaction" target="_blank" rel="noopener"><i class="glyphicon glyphicon-share"></i>Check my transaction</a>' +
                                    '</div>';
                            } else if (['expired', 'cancelled'].includes(response.status)) {
                                if (response.pending_amount < response.amount) {
                                    resultContent += '<div class="invoice__result text-center">' +
                                        '<i class="glyphicon glyphicon-repeat text-primary"></i>' +
                                        '<h3>The order has not been fully paid</h3>' +
                                        '<p>We have received '+ (response.amount - response.pending_amount).toFixed(8) + ' ' + response.currency + ' of ' + elInvoice.dataset.invoiceAmount + ' '
                                        + elInvoice.dataset.invoiceCurrency + ' required. To get your payment back, please, contact store`s support.</p>' +
                                        '</div>';
                                } else {
                                    resultContent += '<div class="invoice__result text-center">' +
                                        '<i class="glyphicon glyphicon-repeat text-primary"></i>' +
                                        '<h3>The order has expired</h3>' +
                                        '<p>Please, <a href="/" title="go back">go back</a> and create a new one.</p>' +
                                        '</div>';
                                }
                            } else if (['error'].includes(response.status)) {
                                resultContent += '<div class="invoice__result text-center">' +
                                    '<i class="glyphicon glyphicon-warning-sign text-danger"></i>' +
                                    '<h3>Ooops...</h3>' +
                                    '<p>Something went wrong with this operation. Please, contact store`s support, so we could figure this out.</p>' +
                                    '</div>';
                                console.error('error');
                            }
                            if (['pending'].includes(response.status)) {
                                elInvoice.querySelector('.invoice__content').innerHTML = resultContent;
                            } else {
                                elInvoice.innerHTML = resultContent;
                            }
                        }
                    });
                }, 15*1000);
            }
        });

        function copyInvoiceValue (el) {
            var textCopyTo = el.parentElement.querySelector('input');
            try {
                textCopyTo.select();
                textCopyTo.focus();
                document.execCommand('copy');
                setTimeout(function () {
                    $(el).tooltip('hide');
                }, 500)
            } catch(err) {
                console.log(err)
            }
        }
    </script>
    <?php endif; ?>

    <?php echo $footer; ?>
