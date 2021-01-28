<?php declare(strict_types=1);

namespace TclTk\Widgets\Ttk;

use TclTk\Options;
use TclTk\Widgets\Widget;

/**
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/ttk_frame.htm
 *
 * @property string $padding
 * @property int $borderWidth
 * @property string $relief
 * @property int $width
 * @property int $height
 * 
 * @todo Implement padding property.
 */
class Frame extends TtkWidget
{
    public const RELIEF_FLAT = 'flat';
    public const RELIEF_GROOVE = 'groove';
    public const RELIEF_RAISED = 'raised';
    public const RELIEF_RIDGE = 'ridge';
    public const RELIEF_SOLID = 'solid';
    public const RELIEF_SUNKEN = 'sunken';

    public function __construct(Widget $parent, array $options = [])
    {
        parent::__construct($parent, 'ttk::frame', 'fr', $options);
    }

    /**
     * @inheritdoc
     */
    protected function initWidgetOptions(): Options
    {
        return new Options([
            'padding' => null,
            'borderWidth' => null,
            'relief' => null,
            'width' => null,
            'height' => null,
        ]);
    }
}