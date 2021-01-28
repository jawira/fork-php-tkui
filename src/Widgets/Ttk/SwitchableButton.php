<?php declare(strict_types=1);

namespace TclTk\Widgets\Ttk;

use TclTk\Options;
use TclTk\Widgets\Buttons\SelectableButton;
use TclTk\Widgets\Widget;

/**
 * The base class for buttons that can be switched.
 */
abstract class SwitchableButton extends GenericButton implements SelectableButton
{
    public function __construct(
        Widget $parent,
        string $widget,
        string $name,
        array $options = []
    ) {
        $var = isset($options['variable']);

        parent::__construct($parent, $widget, $name, $options);

        if (! $var) {
            $this->variable = $this->window()->registerVar($this);
        }
    }

    /**
     * @inheritdoc
     */
    protected function initWidgetOptions(): Options
    {
        return parent::initWidgetOptions()->mergeAsArray([
            'variable' => null,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function select(): self
    {
        $this->setValue(true);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function deselect(): self
    {
        $this->setValue(false);
        return $this;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function getValue()
    {
        return $this->variable->asBool();
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): self
    {
        $this->variable->set($value);
        return $this;
    }
}