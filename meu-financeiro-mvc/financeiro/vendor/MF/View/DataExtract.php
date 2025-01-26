<?php 
    if (isset($this->view->data) && is_array($this->view->data))
        extract($this->view->data);

    if (isset($this->view->settings) && is_array($this->view->settings))
        extract($this->view->settings);
?>