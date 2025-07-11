<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TerritorialUnit;
use App\Models\Street;
use Illuminate\Http\Request;

class TerritorialApiController extends Controller
{
    public function getVoivodeships()
    {
        $voivodeships = TerritorialUnit::getVoivodeships();
        
        return response()->json($voivodeships->map(function ($voivodeship) {
            return [
                'woj' => $voivodeship->woj,
                'nazwa' => $voivodeship->nazwa,
            ];
        }));
    }

    public function getCities($voivodeshipCode)
    {
        $cities = TerritorialUnit::getCitiesForVoivodeship($voivodeshipCode);
        
        return response()->json($cities->map(function ($city) {
            return [
                'woj' => $city->woj,
                'pow' => $city->pow,
                'gmi' => $city->gmi,
                'nazwa' => $city->nazwa,
            ];
        }));
    }

    public function getStreets($voivodeshipCode, $cityCode)
    {
        $streets = Street::getStreetsForCity($voivodeshipCode, $cityCode);
        
        return response()->json($streets->map(function ($street) {
            return [
                'id' => $street->id,
                'sym_ul' => $street->sym_ul,
                'full_name' => $street->getFullNameAttribute(),
                'cecha' => $street->cecha,
                'nazwa_1' => $street->nazwa_1,
                'nazwa_2' => $street->nazwa_2,
            ];
        }));
    }
}