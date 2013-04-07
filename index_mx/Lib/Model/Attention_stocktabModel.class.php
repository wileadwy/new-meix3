<?php

class Attention_stocktabModel extends RelationModel {
    public $_link = array(
        'Stocktab'=>array(
              'mapping_type'=> BELONGS_TO,
              'class_name'=> 'Stocktab',
              'mapping_name' => 'Stocktab',
              'foreign_key' => 'stock_id',
        )

    );
}
?>