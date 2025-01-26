<?php

namespace MF\View;

class SetButtons {
    private array $arr_buttons = [];

    public function setButton($title, $action, $classes)
    {
        $this->arr_buttons[] = [
            'title'   => $title,
            'action'  => $action,
            'classes' => $classes
        ];

        return $this->arr_buttons;
    }

    public function getButtons()
    {
        if (empty($this->arr_buttons)) {
            return false;
        }

        return $this->arr_buttons;
    }

    /**TODO:
     * Fazer opçao na grid q nao exibe nada de investimento
     */
}