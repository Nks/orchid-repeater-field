<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Http\Controllers\Systems;

use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Nakukryskin\OrchidRepeaterField\Http\Requests\RepeaterRequest;
use Orchid\Platform\Http\Controllers\Controller;
use Orchid\Screen\Builder;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Repository;
use ReflectionMethod;
use ReflectionProperty;

class RepeaterController extends Controller
{
    //Template to generate repeater form block. Basically not needed to change.
    const BLOCK_TEMPLATE = 'platform::partials.fields._repeater_block';

    /**
     * @var Rows
     */
    protected $layout;
    /**
     * Repeater name which will be used as prefix.
     *
     * @var array|\Illuminate\Http\Request|string
     */
    protected $repeaterName;

    protected $blocksCount = 0;

    /**
     * How much blocks we need generate at one request.
     *
     * @var array|\Illuminate\Http\Request|int|string
     */
    protected $num = 0;

    /**
     * Values for the current repeater.
     *
     * @var array
     */
    protected $values = [];

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
        $method = new ReflectionMethod($this->layout, 'fields');
        $method->setAccessible(true);

        $queryData = new Repository(collect($query->toArray())->first(null, []));
        $propQuery = new ReflectionProperty($this->layout, 'query');
        $propQuery->setAccessible(true);
        $propQuery->setValue($this->layout, $queryData);

        $fields = $method->invoke($this->layout);

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
            } elseif ($field instanceof Group) {
                $result[] = Group::make($this->prepareFields($field->getGroup()));
            } elseif ($field instanceof Field) {
                $name = $field->get('name');
                //Uses for reorder
                $field->addBeforeRender(function () use ($name, $field) {
                    $propInlineAttributes = new ReflectionProperty($field, 'inlineAttributes');
                    $propInlineAttributes->setAccessible(true);
                    $inlineAttributes = $propInlineAttributes->getValue($field);
                    $inlineAttributes[] = 'data-repeater-name-key';
                    $propInlineAttributes->setValue($field, $inlineAttributes);
                    $field->set('data-repeater-name-key', $name);
                });
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

    /**
     * @param RepeaterRequest $request
     *
     * @return array
     * @throws \Throwable
     */
    public function view(RepeaterRequest $request)
    {
        $layout = Crypt::decryptString($request->get('layout')) ?? null;

        if (! class_exists($layout)) {
            return [];
        }

        $this->layout = app($layout);

        $this->repeaterName = $request->get('repeater_name');
        $this->blocksCount = (int) request('blocks', 0);
        $this->num = (int) $request->get('num', 0);

        if ($request->has('values')) {
            $this->values = (array) request()->get('values', []);
        }

        return $this->handler();
    }
}
