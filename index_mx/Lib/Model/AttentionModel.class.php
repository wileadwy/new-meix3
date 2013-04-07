<?php

class AttentionModel extends RelationModel {
    public $_link = array(
        'User'=>array(
              'mapping_type'=> HAS_ONE,
              'class_name'=> 'User',
              'mapping_name' => 'User',
              'foreign_key' => 'iduser',
        )
    );
}
?>