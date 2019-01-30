WIP

# Orchid Platform Repeater Field

![Orchid Platform Repeater Field](https://user-images.githubusercontent.com/349293/51706442-7df0de00-202f-11e9-9a38-fd1a204c93bc.png)

## Brief
This package adding basic support of repeater field to [Orchid RAD platform](https://github.com/orchidsoftware/platform).

## Pre-requirements
You must have installed and configured [Orchid Platform](https://github.com/orchidsoftware/platform)

## How to use

1. Install package with composer: 
    `composer require nakukryskin/orchid-repeater-field`
    
    Latest version of laravel automatically discover package to use.
    
1. Create a link for assets with `php artisan orchid:repeater:link`. 
    This command will simply create link to assets in your public directory with styling and javascript.
    Package automatically add styles and scripts to your platform then you not needed add those assets to your platform's configuration
    
1. Create `RepeatersFieldsWidget.php` in your `app/Http/Widgets/Repeaters` directory. This widget will be used to add repeaters dynamically.
    Example:
    ```php
    <?php
    
    namespace App\Http\Widgets\Repeaters;
    
    use Nakukryskin\OrchidRepeaterField\Handlers\RepeaterHandler;
    use Orchid\Screen\Fields\InputField;
    use Orchid\Screen\Fields\SelectField;
    
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
                InputField::make('repeater_name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title('Nested Field'),
                //Multiple fields must ends with the dot 
                SelectField::make('select.')
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
                    ->handler(App\Http\Widgets\Repeaters\RepeaterFields::class),
            ];
        }
    ```

1. Open your screen and check that the repeater fields here