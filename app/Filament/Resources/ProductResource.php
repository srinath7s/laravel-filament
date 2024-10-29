<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenAI\Client as OpenAIClient;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use OpenAI\Laravel\Facades\OpenAI;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Name'),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('Price')
                    ->prefix('$'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->nullable(),

                // ChatGPT-like search input
                Forms\Components\TextInput::make('search_query')
                    ->label('Search Query')
                    ->placeholder('Enter your search term...')
                    ->reactive() // React to input changes
                    ->afterStateUpdated(fn($state, callable $set) => self::fetchChatGPTResult($state, $set)),

                // Field to display the result from the search
                Forms\Components\Textarea::make('search_result')
                    ->label('ChatGPT Response')
                    ->placeholder('Result will be shown here...')
                    ->disabled(),

                Forms\Components\Toggle::make('status')
                    ->label('Active Status')
                    ->default(true),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->label('Product Image')
                    ->directory('products')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->maxSize(5120)
                    ->multiple()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->poll('5s')
            ->deferLoading()
            ->striped()
            ->recordClasses(fn(Model $record) => match ($record->status) {
                'draft' => 'opacity-30',
                'reviewing' => 'border-s-2 border-orange-600 dark:border-orange-300',
                'published' => 'border-s-2 border-green-600 dark:border-green-300',
                default => null,
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2)), // Format the price

                Tables\Columns\BooleanColumn::make('status')
                    ->label('Status')
                    // ->trueIcon('heroicon-o-badge-check')
                    // ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    // public static function fetchChatGPTResult($query, callable $set)
    // {
    //     if (empty($query)) {
    //         $set('search_result', ''); // Clear result if no query
    //         return;
    //     }
    //     $client = OpenAIClient::make(env('OPEN_AI_KEY')); // Add your OpenAI API key here

    //     $response = $client->completions()->create([
    //         'model' => 'text-davinci-003', // You can adjust the model
    //         'prompt' => $query,
    //         'max_tokens' => 100, // Define how many tokens (words) you want
    //     ]);

    //     $set('search_result', $response['choices'][0]['text']); // Set the response text in the result field
    // }


    public static function fetchChatGPTResult($query, callable $set)
    {
        if (empty($query)) {
            $set('search_result', ''); // Clear result if no query
            return;
        }
    
        // Make a request to the OpenAI API
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo', // You can use 'gpt-3.5-turbo' or other models
            'messages' => [
                ['role' => 'user', 'content' => $query],
            ],
        ]);
    
        // Set the response in the callback
        $set('search_result', $result->choices[0]->message->content);
    }
    
}
