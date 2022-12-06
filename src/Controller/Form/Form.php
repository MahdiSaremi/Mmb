<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\Controller;
use Mmb\Controller\StepHandler\Handlable;

abstract class Form implements Handlable
{

    /** @var FormStepHandler */
    protected $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function _start()
    {
        $this->startForm();
        $this->go_next = true;
        return $this->_next();
    }

    public function _next()
    {
        $this->current_input = $this->handler->current;
        try {
            $this->stepBeforeForm();
            $this->form();
        }
        catch(FilterError $error)
        {
            return $this->error($error->getMessage());
        }
        catch(FindingInputFinished $finished)
        {
            return $finished->result;
        }
        finally
        {
            $this->stepAfterForm();
            // Save finally values
            $this->handler->inputs = array_map(function ($inp) {
                return $inp->value();
            }, $this->inputs);
            if (isset($this->keyboard))
                $this->handler->key = $this->keyboard;
        }
        return $this->_finish();
    }

    public function _finish()
    {
        $this->endForm();
        return $this->finish();
    }

    public static function request()
    {
        $handler = new FormStepHandler(static::class);
        return $handler->startForm();
    }

    public static function key($text)
    {
        return FormStarter::key($text, 'start', static::class);
    }

    public function startForm()
    {
    }

    public function endForm()
    {
    }

    public function stepBeforeForm()
    {
    }

    public function stepAfterForm()
    {
    }

    public function cancelForm()
    {
    }

    public function error($error)
    {
        replyText($error);
    }

    public function cancel()
    {
        $this->canceled = true;
        throw new FindingInputFinished($this->cancelForm());
    }

    public function keyboard(FormKey $key)
    {
        return [
            $key->options(),
            [ $key->skip("رد کردن") ],
            [ $key->cancel("لغو") ],
        ];
    }

    public function newKey()
    {
        return new FormKey($this);
    }

    public abstract function form();

    public abstract function finish();

    public function getOptions()
    {
        return optional($this->running_input)->getOptions() ?: [];
    }

    protected $_key;

    public function __get($name)
    {
        
        if($name == 'keyboard' || $name == 'key')
        {
            $key = new FormKey($this);
            $res = $this->keyboard($key);
            $res = FormKey::parse($res);
            $this->_key = $res;
            return $this->$name = FormKey::toKey($res);
        }

        return optional($this->inputs[$name])->value();

    }

    /** @var FormInput[] */
    private $inputs = [];
    public $go_next = false;
    public $current_input = '';
    /** @var FormInput */
    public $running_input = null;

    public function required($name)
    {
        $input = (new FormInput($name))->required();
        $this->newInput($input);
    }

    public function optional($name)
    {
        $input = (new FormInput($name))->optional();
        $this->newInput($input);
    }

    public $optionSelected = false;
    public $skipped = false;
    public $canceled = false;
    protected function newInput(FormInput $input)
    {
        try
        {
            $name = $input->name;
            $this->running_input = $input;
            $this->inputs[$name] = $input;

            $input->value($this->handler->inputs[$name] ?? null);

            // Current input
            if($this->current_input == $name)
            {
                $this->$name($input);
                
                // Set value
                $input->value($this->handler->getValue($input, $this->optionSelected, $this->skipped, $this->canceled));

                // Skip
                if($this->skipped)
                {
                    $input->value(null);
                    $input->skip();
                    $this->go_next = true;
                }

                // Cancel
                elseif($this->canceled)
                {
                    if ($input->cancel)
                        throw new FindingInputFinished($input->cancel());
                    else
                        $this->cancel(); // Throw FindingInputFinished
                }

                // Next
                else
                {
                    $input->filled();
                    $this->go_next = true;
                }

                $input->then();

            }

            // Next input
            elseif($this->go_next)
            {

                $this->$name($input);

                // Skip if 'request' not filled
                if ($input->request)
                {
                    $this->go_next = false;
                    $this->handler->current = $name;
                    throw new FindingInputFinished($input->request());
                }

            }
        }
        catch(FilterError $error)
        {

            if($input->error)
            {
                $err = $input->error;
                throw new FindingInputFinished($err($error->getMessage()));
            }
            else
            {
                throw $error;
            }

        }
    }

}
