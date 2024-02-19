<?php

namespace App\Http\Repository\Contracts;

interface IMarkupRepository
{
    public function cpCreateMarkup($InputValues);
    public function cpSearchMarkup($pStayDate, $pCountry = null, $pCity = null);
    public function cpAdvSearchMarkups($pStartDate, $pEndDate, $pCountry = null, $pCity = null);
    public function cpUpdateMarkup($pUpdateValues);
}
