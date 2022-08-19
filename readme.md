# Orchid Platform Repeater Field

[![Support Ukraine Badge](https://bit.ly/support-ukraine-now)](https://supportukrainenow.org)

![Orchid Platform Repeater Field](screenshots/repeater.jpg?raw=true)

## Brief

We've all worked with WordPress at one time or another. Inspired
by [Advanced Custom Fields](https://www.advancedcustomfields.com/).

This package adding repeater fields support to [Orchid RAD platform](https://github.com/orchidsoftware/platform).

## Pre-requirements

You must have installed and configured [Orchid Platform](https://github.com/orchidsoftware/platform)

Version Support: .*

For ^13.0.1 use [^13.0.0 tag](https://github.com/Nks/orchid-repeater-field/tree/13.0.0) and newer.

**Older versions do not have support for most of the current functionality.**

For 6.* version use [2.0.5 tag](https://github.com/Nks/orchid-repeater-field/tree/2.0.5).

For 5 version use [1.0.0 tag](https://github.com/Nks/orchid-repeater-field/tree/1.0.0).

For 4.7.1 version use [0.0.8 tag](https://github.com/Nks/orchid-repeater-field/tree/0.0.8).

## How to use

1. Install package with composer:
   `composer require nakukryskin/orchid-repeater-field`

   Latest version of laravel automatically discover package to use.

2. Create `RepeaterFields.php` in your Orchid Layouts directory.
   Example:

```php
 <?php
 
 namespace App\Http\Orchid\Layouts\Repeaters;
 
 use Orchid\Screen\Layouts\Rows;
 use Orchid\Screen\Fields\Input;
 use Orchid\Screen\Fields\Select;
 
 class RepeaterFields extends Rows
 {
     function fields(): array
     {
         return [
             Input::make('repeater_name')
                 ->type('text')
                 ->max(255)
                 ->required()
                 ->title('Nested Field'),
             
             Select::make('select.')
                 ->multiple()
                 ->options([
                     'test' => 'Test',
                     'test2' => 'Test2'
                 ])
                 ->required()
         ];
     }
 }
```

4. Simply start adding the `RepeaterField::make('repeater')` in your screen:
   Example:

```php
public function layout(): array
{
   return [
       Layout::rows([
           RepeaterField::make('repeater')
               ->title('Repeater')
               ->layout(App\Http\Orchid\Layouts\Repeaters\RepeaterFields::class),
       ])
   ];
}
```

5. Open your screen and check that the repeater fields here

# Advanced usage

Repeater field also support required, max and min parameters. You can add these parameters with call the RepeaterField.

```php
RepeaterField::make('repeater')
    ->title('Repeater')
    ->layout(App\Http\Orchid\Layouts\Repeaters\RepeaterFields::class)
    ->required()
    ->min(10)
    ->max(20)
```

If `->required()` passed to the constructor automatically will set `min` to 1. If the user tries to delete this field,
it will be prevented and show the message.

You can also change the text when deleting a block using the
method `->confirmText('Are you sure that you want to delete the block?')`.

To show required message on save you must add this rule to your screen rules, eg. `'content.*.repeater' => 'required'`

You also can rename button label with your own with method `buttonLabel()`.
eg.

```php
RepeaterField::make('repeater')
    ->title('Repeater')
    ->layout(App\Http\Orchid\Layouts\Repeaters\RepeaterFields::class)
    ->buttonLabel('Add new repeater field')
```

In extreme cases, if you are using some kind of dynamic data loading and you need to pass additional data to your
Layout, use the `->ajaxData()` method. This method can work both with a callable function and with an array of data.
This can be useful when you need to filter the data for each of the fields.

To use ajaxData, first connect trait `AjaxDataAccess` to your Layout:

```php

<?php

namespace App\Orchid\Layouts;

use Nakukryskin\OrchidRepeaterField\Traits\AjaxDataAccess;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;

class RepeaterFields extends Rows
{
    use AjaxDataAccess;

    public function fields(): array
    {
        return [
            Select::make('select.')
                ->title('Select')
                ->multiple()
                ->options($this->getSelectOptions())
                ->required(),
        ];
    }
    
    protected function getSelectOptions() {
        return $this->getAjaxData()->get('select_options');
    }
}
```

After that, on the main screen, determine what you want to transmit. In the example, we generate a list based on the
current user role:


```php

Repeater::make('repeater')
      ->title('Repeater')
      ->layout(\App\Orchid\Layouts\RepeaterFields::class)
      ->ajaxData(function () {
          $data = [
              'select_options' => [
                  'default' => 'Default option',
              ],
          ];
   
          if (request()->user()->id === 1) {
              $data['select_options']['only_first'] = 'Option only for user with id #1';
          }
   
          return $data;
      }),
```

![Dynamic ajax data with anonymous function](screenshots/ajax_data_1.png?raw=true)

You can combine output options and what and when to output to the user in your repeater fields. Note that your Layout
knows nothing about the state of the other fields on the current screen and, if you want to transfer data about the
current fields you must first save the record and use the data and query of your main screen.

```php
Repeater::make('repeater')
         ->title('Repeater')
         ->layout(\App\Orchid\Layouts\RepeaterFields::class)
         ->ajaxData([
             'select_options' => [
                 'default' => $this->query()['name'] ?? 'Default Name',
             ],
         ]),
```

![Using other field from query to](screenshots/ajax_data_2.png?raw=true)
