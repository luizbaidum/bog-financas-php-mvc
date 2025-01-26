<?php 

namespace MF\Entity;

class Entity {
    public function __construct(array $array = []) {
        foreach ($array as $prop => $val) {
            if (property_exists($this, $prop)) {
                $this->$prop = $val;
            }
        }
    }
}
?>