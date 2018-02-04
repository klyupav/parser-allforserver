<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $files = glob('storage/logs/*.log');
        foreach ($files as $key => $value) {
            $files[$key] = str_replace('storage/', '', $value);
        }
        rsort($files);
        return view('logs.index', [
            'files' => $files
        ]);
    }

    public function show($filename)
    {
        $log = file_get_contents("storage/logs/{$filename}");
        echo "<pre>";
        echo $log;
        echo "</pre>";
    }

    public function ajaxFilterLogs(Request $request)
    {
        $query = preg_replace("%[^\d\. \-]%is", '', $request['query']);
        $files = glob("storage/logs/{$query}*.log");
        foreach ($files as $key => $value) {
            $files[$key] = str_replace('storage/', '', $value);
        }
        rsort($files);
        return json_encode([
            'success' => true,
            'files' => $files
        ]);
    }

    public static function logToFile($message, $level = 'info', $context = [])
    {
        // create a log channel
        $log = new Logger(config('app.name'));
        $today = date('Y-m-d');
        $log->pushHandler(new StreamHandler( "storage/logs/{$today}.log"));
        switch ( $level )
        {
            case 'info':
                $log->info($message, $context);
                break;
            case 'warning':
                $log->warning($message, $context);
                break;
            case 'error':
                $log->error($message, $context);
                break;
        }
    }
}
