
<?php echo $header; ?>
<div class="container">
    <div class="row">
        <?php echo $amount; ?> <br/>
        <?php echo $pending_amount; ?> <br/>
        <?php echo $wallet_hash; ?><br/>
        <?php echo $currency; ?><br/>
        <?php echo $expire_utc; ?><br/>

        <img src="<?php echo $qr_code; ?>" />
    </div>
</div>

<?php echo $footer; ?>