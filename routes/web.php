<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

/* ================= VIEW ================= */
Route::view('/komunikasi', 'komunikasi');
Route::view('/gesture', 'gesture');

Route::get('/', function () {
    return view('welcome');
});


/* ================= START AI ================= */
Route::get('/start-ai', function () {

    $python = env('PYTHON_BINARY', 'python');
    $scriptPath = base_path('ai-engine/app.py');

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        pclose(popen("start /B $python \"$scriptPath\"", "r"));
    } else {
        exec("$python \"$scriptPath\" > /dev/null 2>&1 &");
    }

    return response()->json(['status' => 'AI Started']);
});


/* ================= STOP AI ================= */
Route::get('/stop-ai', function () {

    $pidPath = storage_path('app/ai_pid.txt');

    if (File::exists($pidPath)) {

        $pid = trim(File::get($pidPath));

        exec("taskkill /PID $pid /F");

        File::delete($pidPath);

        return response()->json(['status' => 'AI Stopped']);
    }

    return response()->json(['status' => 'AI Not Running']);
});


/* ================= GET GESTURES ================= */
Route::get('/gestures', function () {

    $path = base_path('ai-engine/gestures.json');

    if (!File::exists($path)) {
        return response()->json([]);
    }

    return response()->json(
        json_decode(File::get($path), true)
    );
});


/* ================= ADD GESTURE ================= */
Route::post('/add-gesture', function (Request $request) {

    $path = base_path('ai-engine/gestures.json');

    if (!File::exists($path)) {
        File::put($path, json_encode([], JSON_PRETTY_PRINT));
    }

    $gestures = json_decode(File::get($path), true);

    $gestures[] = [
        "name" => $request->name,
        "text" => $request->text,
        "pattern" => $request->pattern,
        "active" => true
    ];

    File::put($path, json_encode($gestures, JSON_PRETTY_PRINT));

    return response()->json(['status' => 'Gesture Added']);
});


/* ================= UPDATE GESTURE ================= */
Route::post('/update-gesture', function (Request $request) {

    $path = base_path('ai-engine/gestures.json');

    if (!File::exists($path)) {
        return response()->json(['status' => 'File Not Found']);
    }

    $gestures = json_decode(File::get($path), true);

    foreach ($gestures as &$g) {
        if ($g['name'] == $request->name) {
            $g['text'] = $request->text;
            $g['active'] = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
        }
    }

    File::put($path, json_encode($gestures, JSON_PRETTY_PRINT));

    return response()->json(['status' => 'Gesture Updated']);
});

/* ================= DELETE GESTURE ================= */
Route::post('/delete-gesture', function (Illuminate\Http\Request $request) {

    $path = base_path('ai-engine/gestures.json');

    if (!File::exists($path)) {
        return response()->json(['status' => 'File Not Found']);
    }

    $gestures = json_decode(File::get($path), true);

    $gestures = array_values(array_filter($gestures, function ($g) use ($request) {
        return $g['name'] !== $request->name;
    }));

    File::put($path, json_encode($gestures, JSON_PRETTY_PRINT));

    return response()->json(['status' => 'Gesture Deleted']);
});