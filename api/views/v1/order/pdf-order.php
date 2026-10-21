<div style="width: 100%; text-align: center">
    Замовлення №<?php echo $order->id;?>
</div>
<br><br>

<table style="width: 100%;border-collapse: collapse;border: 1px solid black;">
    <?php if(isset($orderData) && !empty($orderData)):?>
        <?php foreach ($orderData as $key => $value):?>
            <tr>
                <td style="border: 1px solid black;"><?php echo $key;?></td>
                <td style="border: 1px solid black;"><?php echo $value;?></td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>
</table>

<br><br>

<table style="width: 100%;border-collapse: collapse;border: 1px solid black;">
    <?php if(isset($productHeader) && !empty($productHeader)):?>
        <thead>
            <tr>
                <?php foreach ($productHeader as $label):?>
                    <th style="border: 1px solid black;"><?php echo $label;?></th>
                <?php endforeach;?>
            </tr>
        </thead>
    <?php endif;?>

    <?php if(isset($products) && !empty($products)):?>
        <?php $amount = 0;?>

        <?php foreach ($products as $product):?>
            <tr>
                <td style="border: 1px solid black;"><?php echo ($product->custom) ? "" : $product->product->sku;?></td>
                <td style="border: 1px solid black;"><?php echo ($product->custom) ? $product->name : $product->product->name;?></td>
                <td style="border: 1px solid black;"><?php echo ($product->custom) ? "шт" : $product->product->unit->name;?></td>
                <td style="border: 1px solid black;"><?php echo $product->quantity;?></td>
                <td style="border: 1px solid black;"><?php echo api\helpers\NumberHelper::convert($product->price);?></td>
                <td style="border: 1px solid black;"><?php echo api\helpers\NumberHelper::convert($product->total_sum);?></td>
            </tr>
            <?php $amount += $product->total_sum;?>
        <?php endforeach;?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="border: 1px solid black;">Всього сума з ПДВ</td>
            <td style="border: 1px solid black;"><?php echo $amount;?></td>
        </tr>
    <?php endif;?>

</table>
