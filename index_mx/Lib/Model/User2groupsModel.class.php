<?php

class User2groupsModel extends RelationModel {
    public $_link = array(
        'User2groups'=>array(
              'mapping_type'=> BELONGS_TO,
              'class_name'=> 'User',
              'mapping_name' => 'User',
              'parent_key' => 'user_id',
        )
    );
}
?>