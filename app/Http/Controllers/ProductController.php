<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\TracksImport;
use App\Models\ClientTrackList;
use App\Models\Configuration;
use App\Models\TrackList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function addChina(Request $request)
    {

        $array =  preg_split("/\s+/", $request["track_codes"]);
        $wordsFromFile = [];
        foreach ($array as $ar){
            $wordsFromFile[] = [
                'track_code' => $ar,
                'to_china' => date(now()),
                'status' => 'Получено в Китае',
                'reg_china' => 1,
                'created_at' => date(now()),
            ];
        }
        TrackList::insertOrIgnore($wordsFromFile);
        return response('success');

    }

    public function almatyIn(Request $request)
    {

        $array =  preg_split("/\s+/", $request["track_codes"]);
        $wordsFromFile = [];
        $city = null;

        if (Auth::user()->type === 'almatyin'){
            $city_field = 'to_almaty';
            $city_value = 'Получено на складе в Алматы';
            $city = 'Алматы';
            $reg_field = 'reg_almaty';
        }elseif (Auth::user()->type === 'karagandain'){
            $city_field = 'to_city';
            $city_value = 'Получено на складе в Караганде';
            $reg_field = 'reg_city';
        }elseif (Auth::user()->type === 'astanain'){
            $city_field = 'to_city';
            $city_value = 'Получено на складе в Астане';
            $city = 'Астана';
            $reg_field = 'reg_city';
        }elseif (Auth::user()->type === 'aktauin'){
            $city_field = 'to_city';
            $city_value = 'Получено на складе в Актау';
            $city = 'Актау';
            $reg_field = 'reg_city';
        }elseif (Auth::user()->type === 'semeyin'){
            $city_field = 'to_city';
            $city_value = 'Получено на складе в Семее';
            $city = 'Семей';
            $reg_field = 'reg_city';
        }

        foreach ($array as $ar){
            $wordsFromFile[] = [
                'track_code' => $ar,
                $city_field => date(now()),
                'status' => $city_value,
                $reg_field => 1,
                'city' => $city,
                'updated_at' => date(now()),
            ];
        }
        TrackList::upsert($wordsFromFile, ['track_code', $city_field, 'status', $reg_field, 'updated_at']);
        return redirect()->back()->with('message', 'Трек коды успешно добавлены');

    }

    public  function acceptProduct (Request $request)
    {
        $track_lists = TrackList::query()->where('track_code', $request->track_code)->first();
        $track_lists->status = 'Товар принят';
        $track_lists->client_accept = date(now());
        $track_lists->save();

        $client_track_lists = ClientTrackList::query()->where('track_code', $request->track_code)->first();
        $client_track_lists->status = 'archive';
        $client_track_lists->save();
        return redirect()->back()->with('message', 'Товар успешно доставлен!');
    }

    public function almatyOut(Request $request)
    {
        if($request["city"] != 'Выберите город' && isset($request["city"])){
            $city = $request["city"];
        }else{
            $city = null;
        }

        if($request["to_city"] != null) {
            $city = $request["to_city"];
        }
        $status = "Выдано клиенту";
        if ($request["send"] === 'true'){
            $status = "Отправлено в Ваш город";
        }
        $array =  preg_split("/\s+/", $request["track_codes"]);
        $client_field = 'to_client';
        if (Auth::user()->type != 'othercity' && Auth::user()->type != 'almatyout'){
            $client_field = 'to_client_city';
        }
        $wordsFromFile = [];
        foreach ($array as $ar){
            $wordsFromFile[] = [
                'track_code' => $ar,
                $client_field => date(now()),
                'status' => $status,
                'reg_client' => 1,
                'city' => $city,
                'updated_at' => date(now()),
            ];
        }
        TrackList::upsert($wordsFromFile, ['track_code', $client_field, 'status', 'city', 'reg_client', 'updated_at']);
        return response('success');

    }
    public function getInfoProduct(Request $request)
    {

        $track_code = ClientTrackList::query()->select('user_id')->where('track_code', $request['track_code'])->first();
        $track_code_statuses =  TrackList::query()->select('to_china', 'to_almaty', 'city', 'to_client', 'client_accept', 'to_city', 'to_client_city')->where('track_code', $request['track_code'])->first();
        if ($track_code){
            $user_data = User::query()->select('name', 'surname', 'login', 'city', 'block', 'address')->where('id', $track_code->user_id)->first();
        }else{
            $user_data = [
                'name' => 'нет',
                'surname' => 'нет',
                'login' => 'нет',
                'block' => 'нет',
                'code' => 'нет',
                'is_post' => false,
                'city' => 'нет',
            ];
        }

        return response([$track_code_statuses, $user_data]);

    }


    public function addClient(Request $request)
    {

        if (Str::length($request["track_code"]) > 100){
            return redirect()->back()->with('error', 'Неверный трек, пожалуйста, перепроверьте');
        }

        $issetTrack = ClientTrackList::query()->where('track_code', $request["track_code"])->exists();
        if ($issetTrack){
            return redirect()->back()->with('error', 'Трек код уже добавлен');
        }
        $track_list = new ClientTrackList();
        $track_list->track_code = $request["track_code"];
        $track_list->detail = $request["detail"];
        $track_list->user_id = Auth::user()->id;
        $track_list->save();

        return redirect()->back()->with('message', 'Трек код успешно добавлен');
    }

    public function deleteTrack (Request $request)
    {
        $validated = $request->validate([
            'delete_track' => 'required|string|max:100',
        ]);

        if ($validated){
            $archive = ClientTrackList::query()->select('id')->where('track_code', $request['delete_track'])->first();
            ClientTrackList::destroy($archive->id);
            return redirect()->back()->with('message', 'Трек код успешно удалён');
        }

    }

    public function archiveProduct (Request $request)
    {
        $validated = $request->validate([
            'archive_track' => 'required|string|max:100',
        ]);

        if ($validated){
            $archive = ClientTrackList::query()->where('track_code', $request['archive_track'])->first();
            $archive->status = 'archive';
            $archive->save();
            return redirect()->back()->with('message', 'Трек код успешно добавлен в архив');
        }

    }

    public function unArchiveProduct (Request $request)
    {
        $validated = $request->validate([
            'archive_track' => 'required|string|max:100',
        ]);

        if ($validated){
            $archive = ClientTrackList::query()->where('track_code', $request['archive_track'])->first();
            $archive->status = null;
            $archive->save();
            return redirect()->back()->with('message', 'Трек код успешно извлечён из архива');
        }

    }

    public function fileImport(Request $request)
    {
        Excel::import(new TracksImport($request['date']), $request->file('file')->store('temp'));
        return back();
    }

    public function fileExport(Request $request)
    {
        return Excel::download(new UsersExport($request['date']), 'users.xlsx');;
    }
    public function result ()
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths(2);

        $chinaTracks = TrackList::select('id', 'to_china', DB::raw("DATE_FORMAT(to_china, '%b') as month_name"))
            ->whereYear('to_china', date('Y'))
            ->whereBetween('to_china', [$startDate, $endDate])
            ->groupBy('to_china')
            ->pluck('id', 'month_name');
        $almatyTracks = TrackList::select('id', 'to_almaty', DB::raw("DATE_FORMAT(to_almaty, '%b') as month_name"))
            ->whereYear('to_almaty', date('Y'))
            ->whereBetween('to_china', [$startDate, $endDate])
            ->groupBy('to_almaty')
            ->pluck('id', 'month_name');
        $clientTracks = TrackList::select('id', 'to_client', DB::raw("DATE_FORMAT(to_client, '%b') as month_name"))
            ->whereYear('to_client', date('Y'))
            ->whereBetween('to_china', [$startDate, $endDate])
            ->groupBy('to_client')
            ->pluck('id', 'month_name');
        $datesTracks = ($chinaTracks)->merge($almatyTracks)->merge($clientTracks);
        $datesTracks = $datesTracks->toArray();

        $labels = array_keys($datesTracks);
        $labels = array_slice($labels, -3);


        $data = [];
        $data2 = [];
        $data3 = [];

        foreach ($labels as $dateT) {
            $data[] = TrackList::query()->where('to_china', 'LIKE', '%-'.str_pad(date_parse($dateT)['month'], 2, '0', STR_PAD_LEFT).'-%')->count();
            $data2[] = TrackList::query()->where('to_almaty', 'LIKE', '%-'.str_pad(date_parse($dateT)['month'], 2, '0', STR_PAD_LEFT).'-%')->count();
            $data3[] = TrackList::query()->where('to_client', 'LIKE', '%-'.str_pad(date_parse($dateT)['month'], 2, '0', STR_PAD_LEFT).'-%')->count();

        }

        $arr = [
            'Jan' => 'Янв.',
            'Feb' => 'Фев.',
            'Mar' => 'Март',
            'Apr' => 'Апр.',
            'May' => 'Май',
            'Jun' => 'Июнь',
            'Jul' => 'Июль',
            'Aug' => 'Авг.',
            'Sep' => 'Сен.',
            'Oct' => 'Окт.',
            'Nov' => 'Ноя.',
            'Dec' => 'Дек.'
        ];
        $labels = array_map(function($v) use($arr) {
            return $arr[$v] ?? $v;
        }, $labels);

        $data = collect($data);
        $data2 = collect($data2);
        $data3 = collect($data3);

        $now = Carbon::now()->format('Y-m-d');
        $targetDay = Carbon::now()->subDays(9)->format('Y-m-d');

        while ($now > $targetDay) {
            $labelsDays[] = Carbon::parse($now)->format('Y-m-d');
            $now = Carbon::parse($now)->sub(1, 'day');
        }

        $dataDays = [];
        $dataDays2 = [];
        $dataDays3 = [];
        $i = 0;
        $monthes = array(
            1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
            5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
            9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
        );
        $days = array(
            'Воскресенье', 'Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота'
        );
        foreach ($labelsDays as $date) {

            $dataDays[$i] = TrackList::query()->where('to_china', 'LIKE', $date . '%')->count();
            $dataDays2[$i] = TrackList::query()->where('to_almaty', 'LIKE', $date . '%')->count();
            $dataDays3[$i] = TrackList::query()->where('to_client', 'LIKE', $date . '%')->count();


            $labelsDays[$i] = date('j', strtotime($date)).' '.$monthes[(date('n', strtotime($date)))]. " \r\n".$days[(date('w', strtotime($date)))];
            $i++;

        }

        $dataDays = collect($dataDays);
        $dataDays2 = collect($dataDays2);
        $dataDays3 = collect($dataDays3);

        $clients = User::query()->where('type', null)->count();
        $clients_today = User::query()->where('type', null)->whereDate('created_at',  Carbon::today())->count();
        $clients_false = User::query()->where('type', null)->where('is_active', false)->count();
        $clients_true = User::query()->where('type', null)->where('is_active', true)->count();
        $clients_auth = User::query()->where('type', null)->whereDate('login_date', Carbon::today())->count();

        $tracks_today = ClientTrackList::query()->whereDate('created_at', Carbon::today())->count();
        $tracks_month = ClientTrackList::query()->where('created_at', 'LIKE', Carbon::now()->format('Y-m').'%')->count();
        $tracks_total = ClientTrackList::query()->count();
        $config = Configuration::query()->select('address', 'title_text')->first();
        return view('result', compact('labels', 'data', 'data2', 'data3', 'clients', 'clients_today',
            'clients_false', 'clients_true', 'clients_auth', 'tracks_today', 'tracks_month', 'tracks_total', 'labelsDays',
            'dataDays', 'dataDays2', 'dataDays3', 'config'));
    }
}
