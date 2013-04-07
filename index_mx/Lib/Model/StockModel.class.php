<?php 
class StockModel extends RelationModel 
{
    public $_link = array(
        'Attention_stock'=>array(
              'mapping_type'=> HAS_MANY,
              'class_name'=> 'Attention_stock',
              'mapping_name' => 'Stock',
              'parent_key' => 'message_id',
        ),
        'Attention_stocktab'=>array(
            'mapping_type'=> HAS_MANY,
            'class_name'=> 'Attention_stocktab',
            'mapping_name' => 'Stocktab',
            'parent_key' => 'message_id',
        ),

    );
}
?>