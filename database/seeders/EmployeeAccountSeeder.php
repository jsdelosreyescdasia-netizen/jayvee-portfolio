<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeAccountSeeder extends Seeder
{
    /**
     * Seed employee login accounts.
     */
    public function run(): void
    {
        $employees = [
            ['Belotindos, Melvin B.', 'mbbelotindos@cdasia.com'],
            ['Bolado, Marvin B.', 'mbbolado@cdasia.com'],
            ['Calara, Analyn S.', 'ascalara@cdasia.com'],
            ['Carel, Celson Lee Karl D.', 'cdcarel@cdasia.com'],
            ['Ceniza, Ergeno B.', 'ebceniza@cdasia.com'],
            ['Clacio, Maria Lorina H.', 'lhlcacio@cdasia.com'],
            ['Contioso, Mona Rica B.', 'mdbelaro@cdasia.com'],
            ['Cruz, Christian Andrew L.', 'clcruz@cdasia.com'],
            ['Cruz, Maria Kristine F.', 'kfcruz@cdasia.com'],
            ['De Los Reyes, Jayvee S.', 'jsdelosreyes@cdasia.com'],
            ['Donaire, Lizamy G.', 'lgdonaire@cdasia.com'],
            ['Epe, Maribeth A.', 'mvase@cdasia.com'],
            ['Estrera, Julie Ann C.', 'jcestrera@cdasia.com'],
            ['Evangelio, Alfonso B. Jr', 'abevangelio@cdasia.com'],
            ['Flores, Graciella C.', 'gcflores@cdasia.com'],
            ['Flores, Willie C. Jr', 'wcflores@cdasia.com'],
            ['Heley, Jonalyn D.', 'jedelarama@cdasia.com'],
            ['Isorena, Cristy P.', 'ccpida@cdasia.com'],
            ['Jamera, Princess Desiree V.', 'dvjamera@cdasia.com'],
            ['Jandusay, Janice M.', 'jvmercado@cdasia.com'],
            ['Kaimo, Ma. Corazon M.', 'cmkaimo@cdasia.com'],
            ['Lamsin, Angelica R.', 'arlamsin@cdasia.com'],
            ['Licuanan, Roscette Melica T.', 'rtlicuanan@cdasia.com'],
            ['Logica, Jon Ray Dien D.', 'jdlogica@cdasia.com'],
            ['Macaballug, Elvis A. Jr', 'eamacaballug@cdasia.com'],
            ['Macaballug, Jenivie A.', 'jamacaballug@cdasia.com'],
            ['Magboo, Steven Jae N.', 'snmagboo@cdasia.com'],
            ['Magnaye, Natasha Pauline G.', 'ngmagnaye@cdasia.com'],
            ['Montero, Jaymark D.', 'jdmontero@cdasia.com'],
            ['Nogas, Janice D.P.', 'jdnogas@cdasia.com'],
            ['Paghubasan, Grace A.', 'gpambe@cdasia.com'],
            ['Pangadlo, Generoso L.', 'glpangadlo@cdasia.com'],
            ['Panogadia, Ma. Catherine T.', 'cmtumagan@cdasia.com'],
            ['Pereira, Al John V.', 'avpereira@cdasia.com'],
            ['Pida, Maricho', 'mcpida@cdasia.com'],
            ['Posadas, Rosalie C.', 'rcposadas@cdasia.com'],
            ['Robles, Joshua Erwin V.', 'jvrobles@cdasia.com'],
            ['Salazar, Edmar A.', 'easalazar@cdasia.com'],
            ['Santiago, Eicee John G.', 'egsantiago@cdasia.com'],
            ['Santos, Joan M.', 'jmsantos@cdasia.com'],
            ['Singson, Lara Liza M.', 'lmsingson@cdasia.com'],
            ['Songco, Gina D.', 'gdsongco@cdasia.com'],
            ['Songco, Rossette A.', 'rasongco@cdasia.com'],
            ['Tuazon, Kristina Leoj D.C.', 'kdtuazon@cdasia.com'],
            ['Vergara, Maylene C.', 'mcvergara@cdasia.com'],
            ['Vidozula, James Ian D.', 'jdividozula@cdasia.com'],
            ['Villanueva, Ervin B.', 'ebvillanueva@cdasia.com'],
            ['Villanueva, Shirley Maine R.', 'srvillanueva@cdasia.com'],
            ['Villaruel, Jonuel Jed B.', 'jbvillaruel@cdasia.com'],
        ];

        foreach ($employees as [$name, $email]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $email,
                    'is_admin' => false,
                ],
            );
        }
    }
}
