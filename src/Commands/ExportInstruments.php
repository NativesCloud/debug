<?php

namespace Vng\EvaCore\Commands;

use Vng\EvaCore\ElasticResources\InstrumentResource;
use Vng\EvaCore\Models\Instrument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportInstruments extends Command
{
    protected $signature = 'eva:export-instruments {mark?}';
    protected $description = 'Create a json file with all instrument data. This json can also be used for an import';

    public function handle(): int
    {
        $this->output->writeln('exporting instruments');

        $instruments = Instrument::all();
        $instrumentResources = collect($instruments)->map(function(Instrument $instrument) {
            return InstrumentResource::make($instrument)->toArray();
        });

        $instrumentsJson = json_encode($instrumentResources, JSON_PRETTY_PRINT);

        $name_prefix = date('dmy');
        $filename = $name_prefix . '-instruments';
        if ($this->hasArgument('mark') && !empty($this->argument('mark'))) {
            $filename = $name_prefix . '-' . $this->argument('mark') . '-instruments';
        }

        Storage::disk('local')->put("exports/{$filename}.json", $instrumentsJson);

        $this->output->writeln('finished');
        return 0;
    }
}
