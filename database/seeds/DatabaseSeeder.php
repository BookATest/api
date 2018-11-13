<?php

use App\Models\Clinic;
use App\Support\Coordinate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $clinics = $this->createClinics(50);
    }

    /**
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createClinics(int $count): Collection
    {
        $clinics = new Collection();

        foreach (range(1, $count) as $index) {
            $coordinate = $this->locationInLeeds();

            $clinics->push(
                factory(Clinic::class)->create([
                    'lat' => $coordinate->getLatitude(),
                    'lon' => $coordinate->getLongitude(),
                ])
            );
        }

        return $clinics;
    }

    /**
     * Generate a coordinate for a random place within Leeds, UK.
     *
     * @return \App\Support\Coordinate
     */
    protected function locationInLeeds(): Coordinate
    {
        $minLat = 53.728531;
        $maxLat = 53.850248;

        $minLon = -1.701228;
        $maxLon = -1.456397;

        $factor = 1000000;

        return new Coordinate(
            mt_rand($minLat * $factor, $maxLat * $factor) / $factor,
            mt_rand($minLon * $factor, $maxLon * $factor) / $factor
        );
    }
}
