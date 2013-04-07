<?php 
class Rec_stocks_cycleModel extends RelationModel 
{
    public $_link = array(
        'Rec_stocks'=>array(
              'mapping_type'=> HAS_MANY,
              'class_name'=> 'Rec_stocks',
              'mapping_name' => 'Rec_stocks',
              'parent_key' => 'rec_stocks_cycle_id',
        ),
        

    );
}
?>