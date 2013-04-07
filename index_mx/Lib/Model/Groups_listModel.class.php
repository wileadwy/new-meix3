<?php 
class Groups_listModel extends RelationModel 
{
    public $_link = array(
        'Groups_discuss'=>array(
              'mapping_type'=> BELONGS_TO,
              'class_name'=> 'Groups_discuss',
              'mapping_name' => 'Groups_discuss',
              'parent_key' => 'groups_discuss_id',
        ),
        'Rec_stocks'=>array(
            'mapping_type'=> BELONGS_TO,
            'class_name'=> 'Rec_stocks',
            'mapping_name' => 'Rec_stocks',
            'parent_key' => 'rec_stocks_id',
        ),
        'Point_view'=>array(
            'mapping_type'=> BELONGS_TO,
            'class_name'=> 'Point_view',
            'mapping_name' => 'Point_view',
            'parent_key' => 'point_view_id',
        ),

    );
}
?>