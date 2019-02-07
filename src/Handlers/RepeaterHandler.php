<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Handlers;

use Illuminate\View\View;
use Orchid\Screen\Builder;
use Orchid\Screen\Field;
use Orchid\Screen\Repository;
use Orchid\Widget\Widget;

/**
 * Modifying the fields to the correct data. Allow to send data to this widget.
 *
 * Class RepeaterHandler
 */
abstract class RepeaterHandler extends Widget
{
    //Template to generate repeater form block. Basically not needed to change.
    const BLOCK_TEMPLATE = 'platform::partials.fields._repeater_block';
    /**
     * Repeater name which will be used as prefix.
     *
     * @var array|\Illuminate\Http\Request|string
     */
    public $repeaterName;


    public $blocksCount = 0;

    /**
     * How much blocks we need generate at one request.
     *
     * @var array|\Illuminate\Http\Request|int|string
     */
    public $num = 1;

    /**
     * Values for the current repeater
     *
     * @var array
     */
    private $values = [];

    public function __construct()
    {
        $this->repeaterName = request('repeater_name', null);
        $this->blocksCount = (int)request('blocks', 0);

        //Maybe we need add more than one block?
        $num = request('num', 1);

        if ($num > 0) {
            $this->num = $num;
        }

        if (request()->has('values')) {
            $this->values = (array)request()->get('values', []);
        }
    }

    /**
     * Return array of the fields.
     *
     * @return Field[]
     */
    abstract public function fields(): array;

    /**
     * Return rendered fields.
     *
     * @return array
     * @throws \Throwable
     */
    final public function handler()
    {
        $result = [
            'template' => view('platform::partials.fields._repeater_field_template')->render(),
            'fields' => [],
        ];

        if ($this->values) {
            foreach ($this->values as $index => $value) {
                $result['fields'][] = $this->build($this->buildRepository($value, $index), $index)->render();
            }
        } else {
            for ($index = 0; $index < $this->num; $index++) {
                $result['fields'][] = $this->build($this->buildRepository([], $index), $index)->render();
            }
        }

        return $result;
    }


    /**
     * Build the form with the repeater fields.
     *
     * @param Repository $query
     * @param int $index
     * @return View
     * @throws \Throwable
     */
    private function build(Repository $query, int $index = 0): View
    {
        $fields = $this->fields();

        $form = new Builder($this->prepareFields($fields), $query);

        if ($this->repeaterName) {
            $form->setPrefix($this->getFormPrefix($index));
        }

        return view(self::BLOCK_TEMPLATE, [
            'form' => $form->generateForm(),
        ]);
    }

    /**
     * Prepare fields for the repeater.
     *
     * @param array $fields
     * @return array
     */
    private function prepareFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            //Preparing group
            if (is_array($field)) {
                $result[] = $this->prepareFields($field);
            } elseif ($field instanceof Field) {
                $name = $field->get('name');
                //Uses for reorder
                $field->attributes['data-repeater-name-key'] = $name;
                $field->inlineAttributes[] = 'data-repeater-name-key';

                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Preparing repository with full form prefix.
     *
     * @param array $data
     * @param int $index
     * @return Repository
     */
    private function buildRepository(array $data = [], int $index = 0): Repository
    {
        return new Repository([$this->getFormPrefix($index) => $data]);
    }

    /**
     * Generate prefix for the form's inputs.
     *
     * @param int $index
     * @return string
     */
    private function getFormPrefix(int $index = 0)
    {
        return $this->repeaterName.'['.($this->blocksCount + $index).']';
    }


}
