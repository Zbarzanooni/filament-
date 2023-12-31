<?php

namespace App\Filament\Resources;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use mysql_xdevapi\Collection;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->required()
                    ->unique(),

                TextInput::make('address')
                    ->required(),

                TextInput::make('phone_number')
                    ->required()
                    ->tel(),

                Forms\Components\Select::make('class_id')
                    ->relationship(name: 'class', titleAttribute: 'name')
                ->reactive(),

                Forms\Components\Select::make('section_id')
                    ->options(function (callable $get){
                        $classId = $get('class_id');

                        if ($classId) {
                            return Section::where('class_id', $classId)->pluck('name','id')->toArray();
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('phone_number')->sortable()->searchable(),
                TextColumn::make('address')->sortable()->searchable()->wrap(),
                TextColumn::make('class.name')->sortable()->searchable(),
                TextColumn::make('section.name')->sortable()->searchable()
            ])
            ->filters([
                Tables\Filters\Filter::make('class_section')
                    ->form([
                        Forms\Components\Select::make('class_id')
                        ->label('Filter of class')
                        ->options(
                            Classes::all()->pluck('name','id')->toArray()
                        ),
                        Forms\Components\Select::make('section_id')
                            ->label('Filter of Section ')
                            ->options(
                                function (callable $get) {
                                            $classId = $get('class_id');
                                    if ($classId) {
                                        return Section::where('class_id', $classId)->pluck('name','id')->toArray();
                                    }
                                }
                            )

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['class_id'],
                                fn (Builder $query, $record): Builder => $query->where('class_id', $record),
                            )
                            ->when(
                                $data['section_id'],
                                fn (Builder $query, $record): Builder => $query->where('section_id', $record),
                            );
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('student selected')
                        ->icon('')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => (new StudentsExport($records))->download('student.xlsx'))
                ]),

            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
