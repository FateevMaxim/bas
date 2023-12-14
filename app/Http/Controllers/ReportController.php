<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Configuration;
use App\Models\TrackList;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getTrackReportPage(){
        $config = Configuration::query()->select('title_text')->first();
        $city = '';
        $date = '';
        $status = '';
        return view('report', compact('config', 'city', 'date', 'status'));
    }

    public function getTrackReport(Request $request){

        $date = '';
        $query = TrackList::query()
            ->select('track_code', 'status');
        if ($request->date != null){
            $query->whereDate('to_client', $request->date);
            $date = $request->date;
        }
        $query->where('status', 'LIKE', 'Отправлено в Ваш город');
        $tracks = $query->with('user')->get();
        $count = $tracks->count();
        $config = Configuration::query()->select('title_text')->first();


        return view('report', compact('tracks',  'config', 'date', 'count'));

    }
}
