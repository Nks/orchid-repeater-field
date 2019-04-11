# Orchid Platform Repeater Field

![Orchid Platform Repeater Field](screenshots/repeater.jpg?raw=true)

## Brief
This package adding basic support of repeater field to [Orchid RAD platform](https://github.com/orchidsoftware/platform).

## Pre-requirements
You must have installed and configured [Orchid Platform](https://github.com/orchidsoftware/platform)

## How to use

1. Install package with composer: 
    `composer require nakukryskin/orchid-repeater-field`
    
    Latest version of laravel automatically discover package to use.
    
1. Create `RepeatersFieldsWidget.php` in your `app/Http/Widgets/Repeaters` directory. This widget will be used to add repeaters dynamically.
    Example:
    ```php
    <?php
    
    namespace App\Http\Widgets\Repeaters;
    
    use Nakukryskin\OrchidRepeaterField\Handlers\RepeaterHandler;
    use Orchid\Screen\Fields\Input;
    use Orchid\Screen\Fields\Select;
    
    class RepeaterFields extends RepeaterHandler
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
        public function fields(): array
        {
            return [
                RepeaterField::make('repeater')
                    ->title('Repeater')
                    ->handler(\App\Http\Widgets\Repeaters\RepeaterFields::class),
            ];
        }
    ```

1. Open your screen and check that the repeater fields here

# Advanced usage

Repeater field also support required, max and min parameters. You can add these parameters with call the RepeaterField.

```php
RepeaterField::make('repeater')
    ->title('Repeater')
    ->handler(\App\Http\Widgets\Repeaters\RepeaterFields::class)
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
    ->handler(App\Http\Widgets\Repeaters\RepeaterFields::class)
    ->button_label('Add new repeater field')
```