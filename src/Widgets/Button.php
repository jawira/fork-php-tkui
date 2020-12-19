<?php declare(strict_types=1);

namespace TclTk\Widgets;

use TclTk\Options;

/**
 * Implementation of Tk button widget.
 *
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/button.htm
 */
class Button extends Widget
{
    public function __construct(TkWidget $parent, string $title, array $options = [])
    {
        $options['text'] = $title;
        parent::__construct($parent, 'button', 'b', $options);
    }

    public function onClick(callable $callback): self
    {
        $this->command = $this->window()->registerCallback($this, $callback);
        return $this;
    }

    /**
     * @link http://www.tcl.tk/man/tcl8.6/TkCmd/button.htm#M16
     */
    public function flash(): void
    {
        $this->exec('flash');
    }

    /**
     * @link http://www.tcl.tk/man/tcl8.6/TkCmd/button.htm#M17
     */
    public function invoke(): void
    {
        $this->exec('invoke');
    }
}