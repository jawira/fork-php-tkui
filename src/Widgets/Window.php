<?php declare(strict_types=1);

namespace TclTk\Widgets;

use TclTk\App;
use TclTk\Exceptions\TclException;
use TclTk\Options;
use TclTk\Interp;
use TclTk\Layouts\Grid;
use TclTk\Layouts\Pack;
use TclTk\Tcl;
use TclTk\Variable;

/**
 * Application window.
 *
 * @property string $title
 * @property string $state
 */
class Window implements Widget
{
    /**
     * The window states.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TkCmd/wm.htm#M62
     */
    const STATE_NORMAL = 'normal';
    const STATE_ICONIC = 'iconic';
    const STATE_WITHDRAWN = 'withdrawn';
    const STATE_ICON = 'icon';
    const STATE_ZOOMED = 'zoomed';

    private App $app;
    private Interp $interp;
    private Options $options;

    /**
     * Child widgets callbacks.
     *
     * Index is the widget path and value - the callback function.
     *
     * @todo must be WeakMap (php8) or polyfill ?
     */
    private array $callbacks;

    /**
     * @var Variable[]
     * @todo WeakMap ?
     */
    private array $vars;

    /**
     * Window instance id.
     */
    private int $id;

    private static int $idCounter = 0;

    /**
     * @todo Create a namespace for window callbacks handler.
     */
    private const CALLBACK_HANDLER = 'PHP_tk_ui_Handler';

    public function __construct(App $app, string $title)
    {
        $this->generateId();
        $this->app = $app;
        $this->interp = $app->tk()->interp();
        $this->callbacks = [];
        $this->vars = [];
        $this->options = $this->initOptions();
        $this->createCallbackHandler();
        $this->createWindow();

        $this->title = $title;
    }

    protected function initOptions(): Options
    {
        return new Options([
            'title' => '',
            'state' => '',
        ]);
    }

    public function __destruct()
    {
        // TODO: unregister callback handler.
        // TODO: destroy all variables.
    }

    private function generateId(): void
    {
        $this->id = static::$idCounter++;
    }

    protected function callbackCommandName(): string
    {
        return self::CALLBACK_HANDLER . '_' . $this->varName();
    }

    protected function createCallbackHandler()
    {
        $this->interp->createCommand($this->callbackCommandName(), function ($path) {
            list ($widget, $callback) = $this->callbacks[$path];
            $callback($widget);
        });
    }

    protected function createWindow(): void
    {
        if ($this->id != 0) {
            $this->app->tclEval('toplevel', $this->path());
        }
    }

    /**
     * @inheritdoc
     */
    public function path(): string
    {
        return '.' . $this->id();
    }

    /**
     * @inheritdoc
     */
    public function id(): string
    {
        return ($this->id === 0) ? '' : 'w' . $this->id;
    }

    public function registerCallback(TkWidget $widget, callable $callback): string
    {
        // TODO: it would be better to use WeakMap.
        //       in that case it will be like this:
        //       $this->callbacks[$widget] = $callback;
        $this->callbacks[$widget->path()] = [$widget, $callback];
        return $this->callbackCommandName() . ' ' . $widget->path();
    }

    /**
     * @inheritdoc
     */
    public function window(): Window
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function widget(): string
    {
        return 'toplevel';
    }

    /**
     * @inheritdoc
     */
    public function options(): Options
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function parent(): Widget
    {
        return $this;
    }

    /**
     * Application instance.
     */
    public function app(): App
    {
        return $this->app;
    }

    public function __get($name)
    {
        return $this->options->$name;
    }

    public function __set($name, $value)
    {
        if ($this->options->has($name) && $this->options->$name !== $value) {
            $this->options->$name = $value;
            // TODO: must be a proxy to "wm" command.
            switch ($name) {
                case 'title':
                    $this->app->tclEval('wm', 'title', $this->path(), Tcl::quoteString($value));
                    break;
                case 'state':
                    $this->app->tclEval('wm', 'state', $this->path(), $value);
            }
        }
    }

    // @todo consider to accept an array of widgets then we can pack
    //       several widgets at once.
    public function pack(Widget $widget, array $options = []): Pack
    {
        return new Pack($widget, $options);
    }

    public function grid(Widget $widget, array $options = []): Grid
    {
        return new Grid($widget, $options);
    }

    /**
     * @param Widget|string $varName
     */
    public function registerVar($varName): Variable
    {
        if ($varName instanceof Widget) {
            $varName = $varName->path();
        }
        if (! isset($this->vars[$varName])) {
            // TODO: variable in namespace ?
            $this->vars[$varName] = $this->interp->createVariable($this->varName(), $varName);
        }
        return $this->vars[$varName];
    }

    /**
     * @param Widget|string $varName
     * @throws TclException When a variable with the specified name is not registered.
     */
    public function unregisterVar($varName): void
    {
        if ($varName instanceof Widget) {
            $varName = $varName->path();
        }
        if (! isset($this->vars[$varName])) {
            throw new TclException(sprintf('Variable "%s" is not registered.', $varName));
        }
        // Implicitly call of Variable's __destruct().
        unset($this->vars[$varName]);
    }

    protected function varName(): string
    {
        return $this->id() ?: 'w0';
    }

    /**
     * @inheritdoc
     */
    public function bind(string $event, ?callable $callback): self
    {
        if ($callback === null) {
            $this->app()->unbind($this, $event);
        } else {
            $this->app()->bind($this, $event, $callback);
        }
        return $this;
    }
}