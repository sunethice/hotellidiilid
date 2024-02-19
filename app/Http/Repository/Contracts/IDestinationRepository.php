<?php

namespace App\Http\Repository\Contracts;

interface IDestinationRepository
{
    public function cpGetDestByCode($pDestinationCode); // used to update description with localization
    public function cpGetDestByZoneCode($pDestinationCode, $pZoneCode); // used to update description of zone with localization
    public function cpGetDestByPhrase($pDestinationPhrase); //used to filter destinations upon entring while searching
    public function cpGetDestByPhraseWithCountry($pDestinationPhrase); //used to filter destinations with country upon entring while searching
    public function cpGetDestWithCountry($pDestinationCode);
    public function cpUpdateDestAttribute($pUpdateValues); // used to update description with localization
    public function cpUpdateZoneDescr($pUpdateValues); // used to update zone description with localization
    public function cpListZonesByDest($pDestinationCode); // list zones by destination
    public function cpIndexByCountryCode($pCountryCode); //list destinations by country code
    public function cpGetCountryCodeOfDest($pDestCode);

    //to be removed
    public function cpIndexDestinations($pClientID);
}
