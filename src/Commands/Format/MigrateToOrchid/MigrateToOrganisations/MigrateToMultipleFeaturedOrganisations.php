<?php

namespace Vng\EvaCore\Commands\Format\MigrateToOrchid\MigrateToOrganisations;

use Vng\EvaCore\Interfaces\OrganisationEntityInterface;
use Vng\EvaCore\Models\Environment;
use Illuminate\Console\Command;

class MigrateToMultipleFeaturedOrganisations extends Command
{
    protected $signature = 'format:migrate-featured-organisations';
    protected $description = 'Migrate the featured organisation to the many to many relationship';

    public function handle(): int
    {
        $this->getOutput()->writeln('starting migrating to multiple featured organisations...');

        $this->call('migrate', ['--force' => true]);

        $this->migrateFeaturedOrganisations();

        $this->getOutput()->writeln('migrating to multiple featured organisations finished!');
        return 0;
    }

    public function migrateFeaturedOrganisations()
    {
        $environments = Environment::all();
        $environments->each(function (Environment $environment) {
            /** @var OrganisationEntityInterface $featuredAssociation */
            $featuredAssociation = $environment->featuredAssociation;
            if ($featuredAssociation instanceof OrganisationEntityInterface) {
                $organisation = $featuredAssociation->getOrganisation();
                $organisation->featuringEnvironments()->syncWithoutDetaching($environment);
            }
        });
    }
}