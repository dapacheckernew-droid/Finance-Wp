<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1><?php echo esc_html(__('Invoice', 'finance-mt')); ?> #<?php echo esc_html($invoice['invoice_number']); ?></h1>
    <p><strong><?php esc_html_e('Customer', 'finance-mt'); ?>:</strong> <?php echo esc_html($invoice['customer_name']); ?></p>
    <p><strong><?php esc_html_e('Issue Date', 'finance-mt'); ?>:</strong> <?php echo esc_html($invoice['issue_date']); ?> | <strong><?php esc_html_e('Due', 'finance-mt'); ?>:</strong> <?php echo esc_html($invoice['due_date']); ?></p>
    <table>
        <thead>
            <tr>
                <th><?php esc_html_e('Item', 'finance-mt'); ?></th>
                <th><?php esc_html_e('Quantity', 'finance-mt'); ?></th>
                <th><?php esc_html_e('Unit Price', 'finance-mt'); ?></th>
                <th><?php esc_html_e('Total', 'finance-mt'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <td><?php echo esc_html($item['item_name']); ?></td>
                    <td><?php echo esc_html($item['quantity']); ?></td>
                    <td><?php echo esc_html(number_format((float)$item['unit_price'], 2)); ?></td>
                    <td><?php echo esc_html(number_format((float)$item['total'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong><?php esc_html_e('Subtotal', 'finance-mt'); ?>:</strong> <?php echo esc_html(number_format((float)$invoice['subtotal'], 2)); ?></p>
    <p><strong><?php esc_html_e('Tax', 'finance-mt'); ?>:</strong> <?php echo esc_html(number_format((float)$invoice['tax'], 2)); ?></p>
    <p><strong><?php esc_html_e('Total', 'finance-mt'); ?>:</strong> <?php echo esc_html(number_format((float)$invoice['total'], 2)); ?></p>
</body>
</html>
