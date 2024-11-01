<?php
if (!$submission->order_items) {
    return '';
}

$currencySetting = \WPPayForm\App\Services\GeneralSettings::getGlobalCurrencySettings($submission->form_id);
$currencySetting['currency_sign'] = \WPPayForm\App\Services\GeneralSettings::getCurrencySymbol($submission->currency);
?>
<div class="wpf_order_items_table_wrapper">
    <table class="table wpf_order_items_table wpf_table table_bordered">
        <thead>
            <th>
                <?php esc_html_e('Item', 'wp-payment-form'); ?>
            </th>
            <th>
                <?php esc_html_e('Quantity', 'wp-payment-form'); ?>
            </th>
            <th>
                <?php esc_html_e('Price', 'wp-payment-form'); ?>
            </th>
           
            <th>
                <?php esc_html_e('Line Total', 'wp-payment-form'); ?>
            </th>
            <?php if ($submission->tax_items->count()): ?>
                <th>
                    <?php esc_html_e('Tax Total', 'wp-payment-form'); ?>
                </th>
                <th>
                    <?php esc_html_e('Gross Total', 'wp-payment-form'); ?>
                </th>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php $subTotal = 0; ?>
            <?php foreach ($submission->order_items as $order_item) {

                if (is_array($order_item)) {
                    if ($order_item['line_total']): ?>
                        <?php $tax_total = 0; ?>
                        <tr>
                            <td style="text-align:center">
                                <?php echo esc_html($order_item['item_name']); ?>
                            </td>
                            <td style="text-align:center">
                                <?php echo esc_html($order_item['quantity']); ?>
                            </td>
                            <td style="text-align:center">
                                <?php echo esc_html(wpPayFormFormattedMoney($order_item['item_price'], $currencySetting)); ?>
                            </td style="text-align:center">
                            <td style="text-align:center">
                                <?php echo esc_html(wpPayFormFormattedMoney($order_item['line_total'], $currencySetting)); ?>
                            </td>
                            <?php foreach ($submission->tax_items as $tax_item) : ?> 
                                <?php 
                                if ($tax_item['parent_holder'] == $order_item['parent_holder']) {
                                    $tax_total += $tax_item->line_total;
                                } ?>
                            <?php endforeach; ?>

                            <?php if ($tax_total) : ?>
                                <td style="text-align:center">
                                    <?php echo esc_html(wpPayFormFormattedMoney($tax_total, $currencySetting)); ?>
                                </td>
                                <td style="text-align:center">
                                    <?php echo esc_html(wpPayFormFormattedMoney($tax_total + $order_item['line_total'], $currencySetting)); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php
                        $subTotal += $order_item['line_total'];
                    endif;
                } else {
                    if ($order_item->line_total): ?>
                        <?php $tax_total = 0; ?>
                        <tr>
                            <td style="text-align:center">
                                <?php echo esc_html($order_item->item_name); ?>
                            </td>
                            <td style="text-align:center">
                                <?php echo esc_html($order_item->quantity); ?>
                            </td>
                            <td style="text-align:center">
                                <?php echo esc_html(wpPayFormFormattedMoney($order_item->item_price, $currencySetting)); ?>
                            </td>
                            <td style="text-align:center">
                                <?php echo esc_html(wpPayFormFormattedMoney($order_item->line_total, $currencySetting)); ?>
                            </td>
                            <?php foreach ($submission->tax_items as $tax_item) : ?> 
                                <?php 
                                if ($tax_item->parent_holder == $order_item->parent_holder) {
                                    $tax_total += $tax_item->line_total;
                                } ?>
                            <?php endforeach; ?>

                            <?php if ($tax_total) : ?>
                                <td style="text-align:center">
                                    <?php echo esc_html(wpPayFormFormattedMoney($tax_total, $currencySetting)); ?>
                                </td>
                                <td style="text-align:center">
                                    <?php echo esc_html(wpPayFormFormattedMoney($tax_total + $order_item->line_total, $currencySetting)); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php
                        $subTotal += $order_item->line_total;
                    endif;
                }
            }
            ;
            ?>
        </tbody>
        <tfoot>
            <?php $discountTotal = 0;
            if (isset($submission->discounts['applied']) && count($submission->discounts['applied'])): ?>
                <tr class="wpf_total_row">
                    <th style="text-align: right" colspan="3">
                        <?php esc_html__('Sub-Total', 'wp-payment-form'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(wpPayFormFormattedMoney($subTotal, $currencySetting)); ?>
                    </td>
                </tr>
                <?php
                foreach ($submission->discounts['applied'] as $discount):
                    $discountTotal += intval($discount->line_total);
                    ?>
                    <tr class="wpf_discount_row">
                        <th style="text-align: right" colspan="3">
                            <?php echo esc_html__('Discounts') . ' (' . esc_html($discount->item_name) . ')'; ?>
                        </th>
                        <td>
                            <?php echo '-' . esc_html(wpPayFormFormattedMoney($discount->line_total, $currencySetting)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($submission->tax_items->count()): ?>
                <tr class="wpf_sub_total_row">
                    <th style="text-align: right" colspan="5">
                        <?php esc_html__('Sub Total', 'wp-payment-form'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(wpPayFormFormattedMoney($subTotal - $discountTotal, $currencySetting)); ?>
                    </td>
                </tr>
                <?php foreach ($submission->tax_items as $tax_item): ?>
                    <tr class="wpf_sub_total_row">
                        <td style="text-align: right" colspan="5">
                            <?php echo esc_html($tax_item->item_name); ?>
                        </td>
                        <td>
                            <?php echo esc_html(wpPayFormFormattedMoney($tax_item->line_total, $currencySetting)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr class="wpf_total_payment_row">
                <th style="text-align: right" 
                    colspan="
                        <?php if ($submission->tax_items->count()): ?>
                               <?php echo '5' ?> 
                        <?php else: ?> 
                            <?php echo '3' ?> 
                        <?php endif  ?>
                    ">
                    <?php esc_html__('Total', 'wp-payment-form'); ?>
                </th>
                <td>
                    <?php echo esc_html(wpPayFormFormattedMoney(intval($submission->payment_total), $currencySetting)); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
if ($submission->payment_method === 'paypal' && $submission->payment_status == 'pending') { ?>
    <div style="background: #f7fafc; border: 1px solid #cac8c8; padding: 10px; font-size:13px; margin-bottom: 12px;">
        <h3><?php esc_html_e('Payment is not marked as paid yet.', 'wp-payment-form') ?></h3>
        <?php esc_html_e('Sometimes, PayPal payments take a few moments to mark as paid! Try reloading receipt page after sometime.', 'wp-payment-form') ?>
        <div class="wpf_pending-loader">
            <div class="spinner"></div>
            <div class="countdown"></div>
        </div>
    </div>
<?php } ?>
