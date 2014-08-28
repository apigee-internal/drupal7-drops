<?php
/**
 * Author: isaias@apigee.com
 * Date: 12/27/13
 * Time: 8:33 AM
 */
?>
<div id="topUpBalanceContainer" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="topUpLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="topUpLabel">Top Up Prepaid Balance <span id="currency_title"></span></h3>
    <div id="topup_alert_minimum_required" class="alert alert-error hide">
      You have to top up your balance to at least <span id="span-minimum-amount">0</span>.
    </div>
    <div id="topup_alert_maximum_required" class="alert alert-error hide">
      You cannot top up your balance with more than <span id="span-maximum-amount">0</span>.
    </div>
  </div>
  <div class="modal-body">
    <p>To top up your prepaid balance you will be taken to World Pay to process your payment.<br>Please enter the desired balance amount below.</p>
    <?php print drupal_render($form['currency_id']); ?>
    <?php print drupal_render($form['current_balance']); ?>
    <?php print drupal_render($form['top_up_amount']); ?>
    <?php print drupal_render($form['new_balance']); ?>
  </div>
  <div class="modal-footer">
    <?php print drupal_render($form['submit']); ?>
    <a class="btn" data-dismiss="modal" aria-hidden="true">Cancel</a>
  </div>
</div>
<?php print drupal_render_children($form); ?>
