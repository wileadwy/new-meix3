<?php

class Attention_stockModel extends RelationModel {
    public $_link = array(
        'Attention_stock'=>array(
              'mapping_type'=> BELONGS_TO,
              'class_name'=> 'Stock',
              'mapping_name' => 'Stock',
              'parent_key' => 'stock_id',
        )

    );
}
?>