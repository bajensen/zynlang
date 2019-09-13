<?php
namespace ZynLang\Html;

class ElementA extends Base {

    /**
     * @param string $location
     * @return $this
     */
    public function href($location) {
        return $this->attr('href', $location);
    }

    /**
     * @param string $target
     * @return $this
     */
    public function target($target) {
        return $this->attr('target', $target);
    }

    /**
     * @param string $target
     * @return $this
     */
    public function title($target) {
        return $this->attr('title', $target);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name($name) {
        return $this->attr('name', $name);
    }
}