<?php

namespace App\Providers;

use App\Http\Repository\Contracts\IAuthRepository;
use App\Http\Repository\Contracts\IBoardRepository;
use App\Http\Repository\Contracts\IBookingRepository;
use App\Http\Repository\Contracts\ICancelRequestRepository;
use App\Http\Repository\Contracts\ICategoryRepository;
use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Repository\Contracts\IDestinationRepository;
use App\Http\Repository\Contracts\IEloquentRepository;
use App\Http\Repository\Contracts\IFacilityGroupRepository;
use App\Http\Repository\Contracts\IFacilityRepository;
use App\Http\Repository\Contracts\IGeneralConfigRepository;
use App\Http\Repository\Contracts\IGroupCategoryRepository;
use App\Http\Repository\Contracts\IHotelbedsCredentialsRepository;
use App\Http\Repository\Contracts\IHotelImageRepository;
use App\Http\Repository\Contracts\IHotelRepository;
use App\Http\Repository\Contracts\IImageTypeRepository;
use App\Http\Repository\Contracts\IMarkupRepository;
use App\Http\Repository\Contracts\IRoomRepository;
use App\Http\Repository\MongoRepository\AuthRepository;
use App\Http\Repository\MongoRepository\BaseRepository;
use App\Http\Repository\MongoRepository\BoardRepository;
use App\Http\Repository\MongoRepository\BookingRepository;
use App\Http\Repository\MongoRepository\CancelRequestRepository;
use App\Http\Repository\MongoRepository\CategoryRepository;
use App\Http\Repository\MongoRepository\CountryRepository;
use App\Http\Repository\MongoRepository\DestinationRepository;
use App\Http\Repository\MongoRepository\FacilityGroupRepository;
use App\Http\Repository\MongoRepository\FacilityRepository;
use App\Http\Repository\MongoRepository\GeneralConfigRepository;
use App\Http\Repository\MongoRepository\GroupCategoryRepository;
use App\Http\Repository\MongoRepository\HotelbedsCredentialsRepository;
use App\Http\Repository\MongoRepository\HotelImageRepository;
use App\Http\Repository\MongoRepository\HotelRepository;
use App\Http\Repository\MongoRepository\ImageTypeRepository;
use App\Http\Repository\MongoRepository\MarkupRepository;
use App\Http\Repository\MongoRepository\RoomRepository;
use App\Models\Country;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IEloquentRepository::class, BaseRepository::class);
        $this->app->bind(IAuthRepository::class,AuthRepository::class);
        $this->app->bind(IMarkupRepository::class, MarkupRepository::class);
        $this->app->bind(IHotelbedsCredentialsRepository::class, HotelbedsCredentialsRepository::class);
        $this->app->bind(IBoardRepository::class, BoardRepository::class);
        $this->app->bind(ICategoryRepository::class, CategoryRepository::class);
        $this->app->bind(IGroupCategoryRepository::class, GroupCategoryRepository::class);
        $this->app->bind(IFacilityRepository::class, FacilityRepository::class);
        $this->app->bind(IFacilityGroupRepository::class, FacilityGroupRepository::class);
        $this->app->bind(ICountryRepository::class, CountryRepository::class);
        $this->app->bind(IDestinationRepository::class, DestinationRepository::class);
        $this->app->bind(IRoomRepository::class, RoomRepository::class);
        $this->app->bind(IImageTypeRepository::class, ImageTypeRepository::class);
        $this->app->bind(IHotelRepository::class, HotelRepository::class);
        $this->app->bind(IHotelImageRepository::class, HotelImageRepository::class);
        $this->app->bind(IBookingRepository::class, BookingRepository::class);
        $this->app->bind(IGeneralConfigRepository::class, GeneralConfigRepository::class);
        $this->app->bind(ICancelRequestRepository::class, CancelRequestRepository::class);
    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
