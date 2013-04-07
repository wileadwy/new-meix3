<?php

class GroupsModel extends RelationModel{
    public $_link = array(
        'User'=>array(
              'mapping_type'=> MANY_TO_MANY,
              'class_name'=> 'User',
              'mapping_name' => 'User',
              'foreign_key' => 'groups_id',
              'relation_foreign_key'=>'user_id',
			  'relation_table'=>'mx_user2groups',
			  'mapping_fields'=>'iduser,name,avatar'
//			  'mapping_limit'=>'9'
        )
    );
}
?>