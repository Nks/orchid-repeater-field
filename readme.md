# Orchid Platform Repeater Field

![Orchid Platform Repeater Field](screenshots/repeater.jpg?raw=true)

## Brief
This package adding repeater fields support to [Orchid RAD platform](https://github.com/orchidsoftware/platform).

## Pre-requirements
You must have installed and configured [Orchid Platform](https://github.com/orchidsoftware/platform)

Version Support: .*

For 6.* version use [2.0.5 tag](https://github.com/Nks/orchid-repeater-field/tree/2.0.5)

For 5 version use [1.0.0 tag](https://github.com/Nks/orchid-repeater-field/tree/1.0.0). 

For 4.7.1 version use [0.0.8 tag](https://github.com/Nks/orchid-repeater-field/tree/0.0.8).

## How to use

1. Install package with composer: 
    `composer require nakukryskin/orchid-repeater-field`
    
    Latest version of laravel automatically discover package to use.
    
1. Create `RepeaterFields.php` in your Orchid Layouts directory. 
    Example:
    ```php
    <?php
    
    namespace App\Http\Orchid\Layouts\Repeaters;
    
    use Orchid\Screen\Layouts\Rows;
    use Orchid\Screen\Fields\Input;
    use Orchid\Screen\Fields\Select;
    
    class RepeaterFields extends Rows
    {
    
        /**
         * Return array of the fields
         *
         * @return array
         */
        function fields(): array
        {
            return [
                Input::make('repeater_name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title('Nested Field'),
                //Multiple fields must ends with the dot 
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

1. Simply start adding the `RepeaterField::make('repeater')` in your screen:
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

1. Open your screen and check that the repeater fields here

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

If `required()` passed to the constructor `min()` automatically will set to 1. If user will try delete this field it will be prevented and show the message.

To show required message on save you must add this rule to your screen rules, eg. `'content.*.repeater' => 'required'`

You also can rename button label with your own with method `button_label()`. 
eg.
```php
RepeaterField::make('repeater')
    ->title('Repeater')
    ->layout(App\Http\Orchid\Layouts\Repeaters\RepeaterFields::class)
    ->button_label('Add new repeater field')
```
