<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    public function store(Request $request)
    {
        $technicalDetails = "";
        if (!empty($request->stack)) {
            $technicalDetails = "STACK (Trace):\n" . substr($request->stack, 0, 1000);
        } elseif (!empty($request->data)) {
            $dataJson = json_encode($request->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $technicalDetails = "DATA:\n" . $dataJson;
        } else {
            $technicalDetails = "No additional technical details provided.";
        }

        $separator = str_repeat("=", 100);

        $content = "\n{$separator}\n";
        $content .= "URL     : {$request->url}\n";
        $content .= "MESSAGE : {$request->message}\n";
        $content .= "INFO    : " . ($request->info ?? 'N/A') . "\n";
        $content .= "AGENT   : " . $request->userAgent() . "\n";
        $content .= $technicalDetails . "\n";
        $content .= "{$separator}" . PHP_EOL;

        Log::channel('vue')->error($content);

        return response()->json(['status' => 'ok']);
    }
}
