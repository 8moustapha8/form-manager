<?php
namespace FormManager\Traits;

/**
 * Class with common methods for all inputs with DataElementInterface.
 */
trait InputTrait
{
    use NodeTreeTrait;

    /**
     * @see FormManager\DataElementInterface
     *
     * {@inheritdoc}
     */
    public function val($value = null)
    {
        if ($value === null) {
            return $this->attr('value');
        }

        if ($this->attr('multiple') && !is_array($value)) {
            $value = array($value);
        }

        return $this->attr('value', $value);
    }

    /**
     * @see FormManager\DataElementInterface
     *
     * {@inheritdoc}
     */
    public function load($value = null)
    {
        if ($this->sanitizer === null) {
            $this->val($value === null ? '' : $value);

            return $this;
        }

        if ($this->attr('multiple') && is_array($value)) {
            foreach ($value as &$val) {
                $val = call_user_func($this->sanitizer, $val);
            }
        } else {
            $value = call_user_func($this->sanitizer, $value);
        }

        $this->val($value === null ? '' : $value);

        return $this;
    }

    /**
     * Checks the input (used in some inputs like radio/checkboxes).
     *
     * @return $this
     */
    public function check()
    {
        return $this;
    }

    /**
     * Unchecks the input  (used in some inputs like radio/checkboxes).
     *
     * @return $this
     */
    public function uncheck()
    {
        return $this;
    }

    /**
     * Calculate the input name on print.
     *
     * {@inheritdoc}
     */
    protected function attrToHtml()
    {
        if ($this->getParent()) {
            $this->attributes['name'] = $this->generateName();
        }

        return parent::attrToHtml();
    }

    /**
     * @see FormManager\DataElementInterface
     *
     * {@inheritdoc}
     */
    public function attr($name = null, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->attr($k, $v);
            }

            return $this;
        }

        if ($name === 'name' && $this->getParent()) {
            if ($value === null) {
                return $this->generateName();
            }

            throw new \InvalidArgumentException('The attribute "name" is read only!');
        }

        if ($value !== null) {
            $class = 'FormManager\\Attributes\\'.ucfirst($name);

            if (class_exists($class) && method_exists($class, 'onAdd')) {
                $value = $class::onAdd($this, $value);
            }
        }

        return parent::attr($name, $value);
    }

    /**
     * Generate the right name attribute for this input
     *
     * @return string
     */
    protected function generateName()
    {
        $name = $this->getPath();

        if ($this->attr('multiple')) {
            $name .= '[]';
        }

        return $name;
    }
}
