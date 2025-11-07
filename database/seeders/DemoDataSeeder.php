<?php

namespace Database\Seeders;

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Repositories\TypeRepository;
use Webkul\User\Models\User;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo Persons and Leads.
     *
     * @return void
     */
    public function run()
    {
        $faker = FakerFactory::create();

        /**
         * Basic guards: make sure required reference data exists.
         */
        $userId = User::query()->value('id');
        if (! $userId) {
            $this->command?->warn('No users found; skipping demo seed. Run installer first.');
            return;
        }

        /** @var PipelineRepository $pipelineRepository */
        $pipelineRepository = app(PipelineRepository::class);
        $pipeline = $pipelineRepository->getDefaultPipeline();
        $stage = $pipeline?->stages->first();
        if (! $pipeline || ! $stage) {
            $this->command?->warn('No pipeline/stage found; skipping demo seed.');
            return;
        }

        /** @var SourceRepository $sourceRepository */
        $sourceRepository = app(SourceRepository::class);
        $leadSourceId = $sourceRepository->query()->value('id');

        /** @var TypeRepository $typeRepository */
        $typeRepository = app(TypeRepository::class);
        $leadTypeId = $typeRepository->query()->value('id');

        DB::beginTransaction();
        try {
            // Create demo persons (via model to avoid repository attribute hooks)
            $persons = [];
            for ($i = 0; $i < 20; $i++) {
                $name = $faker->name();
                $email = $faker->unique()->safeEmail();

                $persons[] = Person::query()->create([
                    'name'            => $name,
                    'emails'          => [['label' => 'work', 'value' => $email, 'primary' => true]],
                    'contact_numbers' => [['label' => 'mobile', 'value' => $faker->e164PhoneNumber()]],
                    'job_title'       => $faker->jobTitle(),
                    'user_id'         => $userId,
                ]);
            }

            // Create demo leads (via model to avoid repository attribute hooks)
            for ($i = 0; $i < 50; $i++) {
                $person = $faker->randomElement($persons);

                Lead::query()->create([
                    'title'                  => $faker->bs(),
                    'description'            => $faker->sentence(8),
                    'lead_value'             => $faker->numberBetween(500, 50000),
                    'status'                 => 1,
                    'expected_close_date'    => $faker->optional()->date('Y-m-d'),
                    'user_id'                => $userId,
                    'lead_source_id'         => $leadSourceId,
                    'lead_type_id'           => $leadTypeId,
                    'lead_pipeline_id'       => $pipeline->id,
                    'lead_pipeline_stage_id' => $stage->id,
                    'person_id'              => $person->id,
                ]);
            }

            DB::commit();

            $this->command?->info('Demo persons and leads seeded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error('Demo seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
