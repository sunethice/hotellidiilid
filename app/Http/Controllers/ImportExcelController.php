<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IBoardRepository;
use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Repository\Contracts\IDestinationRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel as Excel;

class ImportExcelController extends Controller
{
    use JsonResponseTrait;
    private $cICountryRepository;
    private $cIDestinationRepository;
    private $cIBoardRepository;

    public function __construct(ICountryRepository $pICountryRepository, IDestinationRepository $pIDestinationRepository,IBoardRepository $pIBoardRepository)
    {
        $this->cICountryRepository = $pICountryRepository;
        $this->cIDestinationRepository = $pIDestinationRepository;
        $this->cIBoardRepository = $pIBoardRepository;
    }

    public function index()
    {
        $data = DB::table('tbl_customer')->orderBy('CustomerID', 'DESC')->get();
        return view('import_excel', compact('data'));
    }

    public function cpImport(Request $request)
    {
        $this->validate($request, [
            'select_file'  => 'required|string'
        ]);
        $path = $request['select_file'];
        $data = Excel::toArray([], public_path() . "/" . $path);
        $insert_data = [];
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                foreach ($value as $row) {
                    // Log::info("row");
                    // Log::info($row);
                    if (!empty($row[2])) {
                        $updateValues = array(
                            // "attribute" => 'name',
                            'lang' => 'es',
                            'description' => $row['2'],
                            'board_code' => $row['0']
                        );
                        // Log::info($row['1']);
                        // $this->cIDestinationRepository->cpUpdateDestAttribute($updateValues);
                        // $this->cICountryRepository->cpUpdateCountryDescr($updateValues);
                        $this->cIBoardRepository->cpUpdateBoardDescr($updateValues);
                    }
                }
            }
            // if (!empty($insert_data)) {
            //     DB::table('tbl_customer')->insert($insert_data);
            // }
        }
        return back()->with('success', 'Excel Data Imported successfully.');
    }
}
