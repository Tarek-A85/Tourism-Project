<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Airport;
class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       foreach(["Los Angeles International Airport (LAX) - Los Angeles",
                "Heathrow Airport (LHR) - London",
                "Charles de Gaulle Airport (CDG) - Paris",
                "Tokyo Haneda Airport (HND) - Tokyo",
                "Dubai International Airport (DXB) - Dubai",
                "Frankfurt Airport (FRA) - Frankfurt",
                "Changi Airport (SIN) - Singapore",
                "Sydney Kingsford Smith Airport (SYD) - Sydney",
                "Hartsfield-Jackson Atlanta International Airport (ATL) - Atlanta",
                "São Paulo/Guarulhos–Governador André Franco Montoro International Airport (GRU) - São Paulo",
                "John F. Kennedy International Airport (JFK) - New York City",
                "Hong Kong International Airport (HKG) - Hong Kong",
                "Amsterdam Schiphol Airport (AMS) - Amsterdam",
                "Incheon International Airport (ICN) - Seoul",
                "Madrid-Barajas Adolfo Suárez Airport (MAD) - Madrid",
                "San Francisco International Airport (SFO) - San Francisco",
                "Chicago O'Hare International Airport (ORD) - Chicago",
                "Beijing Capital International Airport (PEK) - Beijing",
                "Toronto Pearson International Airport (YYZ) - Toronto",
                "Miami International Airport (MIA) - Miami",
                "Denver International Airport (DEN) - Denver",
                "Narita International Airport (NRT) - Tokyo",
                "Singapore Changi Airport (SIN) - Singapore",
                "Barcelona–El Prat Airport (BCN) - Barcelona",
                "Vancouver International Airport (YVR) - Vancouver",
                "Suvarnabhumi Airport (BKK) - Bangkok",
                "Indira Gandhi International Airport (DEL) - Delhi",
                "Mexico City International Airport (MEX) - Mexico City",
                "Seattle-Tacoma International Airport (SEA) - Seattle",
                "Gatwick Airport (LGW) - London",
                "Brisbane Airport (BNE) - Brisbane",
                "Kuala Lumpur International Airport (KUL) - Kuala Lumpur",
                "Istanbul Airport (IST) - Istanbul",
                "Dublin Airport (DUB) - Dublin",
                "Zurich Airport (ZRH) - Zurich",
                "Munich Airport (MUC) - Munich",
                "Vienna International Airport (VIE) - Vienna",
                "Copenhagen Airport (CPH) - Copenhagen",
                "Lisbon Portela Airport (LIS) - Lisbon",
                "Doha Hamad International Airport (DOH) - Doha",
                "Cape Town International Airport (CPT) - Cape Town",
                "Brussels Airport (BRU) - Brussels",
                "Stockholm Arlanda Airport (ARN) - Stockholm",
                "Helsinki-Vantaa Airport (HEL) - Helsinki",
                "Milan Malpensa Airport (MXP) - Milan",
                "Rome Fiumicino Airport (FCO) - Rome",
                "Manchester Airport (MAN) - Manchester",
                "Athens International Airport (ATH) - Athens",
                "Oslo Gardermoen Airport (OSL) - Oslo",
                "Rio de Janeiro-Galeão International Airport (GIG) - Rio de Janeiro"
                    ]
                as $airport){
        Airport::create([
            'name' => $airport,
        ]);
        
       }
    }
}
