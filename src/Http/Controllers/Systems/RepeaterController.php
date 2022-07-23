<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Http\Controllers\Systems;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Nakukryskin\OrchidRepeaterField\Exceptions\UnsupportedAjaxDataLayout;
use Nakukryskin\OrchidRepeaterField\Exceptions\WrongLayoutPassed;
use Nakukryskin\OrchidRepeaterField\Http\Requests\RepeaterRequest;
use Nakukryskin\OrchidRepeaterField\Traits\AjaxDataAccess;
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
    const BLOCK_TEMPLATE = 'platform::partials.fields._repeater_block';

    protected Rows $layout;
    protected string $repeaterName;
    protected int $blocksCount = 0;

    /**
     * How many blocks we need generate at one request.
     */
    protected \Illuminate\Http\Request|string|array|int $num = 0;

    /**
     * Values for the current repeater.
     */
    protected array $values = [];

    /**
     * Ajax values for the current repeater.
     */
    protected ?array $repeaterData = null;

    /**
     * Return rendered fields.
     */
    final public function handler(): array
    {
        $result = [
            'template' => view('platform::partials.fields._repeater_field_template')->render(),
            'fields'   => [],
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

    private function prepareFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                $result[] = $this->prepareFields($field);
            } elseif ($field instanceof Group) {
                $result[] = Group::make($this->prepareFields($field->getGroup()));
            } elseif ($field instanceof Field) {
                $name = $field->get('name');

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

    private function buildRepository(array $data = [], int $index = 0): Repository
    {
        return new Repository([
            $this->getFormPrefix($index) => array_merge($data, ['_repeater_data' => $this->repeaterData]),
        ]);
    }

    private function getFormPrefix(int $index = 0): string
    {
        return $this->repeaterName.'['.($this->blocksCount + $index).']';
    }

    public function view(RepeaterRequest $request): array
    {
        $layout = Crypt::decryptString($request->get('layout')) ?? null;

        throw_if(! class_exists($layout), new WrongLayoutPassed($layout));

        $this->layout = app($layout);

        $this->repeaterName = $request->get('repeater_name');
        $this->blocksCount = $request->get('blocks', 0);
        $this->num = $request->get('num', 0);
        $this->repeaterData = $request->get('repeater_data', []);

        if (! is_null($this->repeaterData) && ! in_array(AjaxDataAccess::class, class_uses($this->layout), true)) {
            throw new UnsupportedAjaxDataLayout(get_class($this->layout));
        }

        if ($request->has('values')) {
            $this->values = $request->get('values');
        }

        return $this->handler();
    }
}
