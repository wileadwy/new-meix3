<?php
class ThemeWidget extends Widget{
    public function render($data){
        if(!empty($data['theme'])){
            $content = $this->renderFile($data['theme'],$data);
        }else{
            $content = false;
        }
        return $content;
    }
}

?>
