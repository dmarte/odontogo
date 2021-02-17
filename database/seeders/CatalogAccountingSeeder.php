<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CatalogAccountingSeeder extends Seeder
{
    public function run()
    {
        $items = collect(config("ogo.DO.catalog"));
        $keys = strlen($items->keys()->max());
        $command = $this->command;

        for ($i = 1; $i <= $keys; $i++) {

            $items->each(function ($name, $code) use ($command) {

                $command->info("{$code} - {$name}");

                Attribute::updateOrCreate([
                    'kind'           => Attribute::KIND_CATALOG_ACCOUNTING,
                    'name'           => $name,
                    'code'           => $code,
                    'system_default' => true,
                ]);

            });

        }
    }


    private function collect(Collection $items, $position = 1)
    {

        return $items->filter(fn($name, $code) => strlen($code) === $position);
    }
}
