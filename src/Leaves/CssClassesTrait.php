<?php

namespace Rhubarb\Leaf\Leaves;

trait CssClassesTrait
{
    private $cssClasses = [];

    public function addCssClass($className)
    {
        $this->cssClasses[] = $className;
        $this->updateModel();
        return $this;
    }

    public function addCssClasses()
    {
        $classes = func_get_args();

        if (isset($classes[0])) {
            $classes = $classes[0];
        }

        $this->cssClasses = array_merge($this->cssClasses, $classes);
        $this->updateModel();
        return $this;
    }

    /**
     * @deprecated
     * @param array ...$classes
     */
    public function addCssClassNames(...$classes)
    {
        $this->addCssClasses($classes);
    }

    public function removeCssClasses(...$classes)
    {
        $this->cssClasses = array_diff($this->cssClasses, $classes);
        $this->updateModel();
        return $this;
    }

    public function getCssClasses()
    {
        return $this->cssClasses;
    }

    private function updateModel()
    {
        if (isset($this->model) && $this->model instanceof LeafModel) {
            $this->model->cssClassNames = $this->cssClasses;
        }
    }
}
