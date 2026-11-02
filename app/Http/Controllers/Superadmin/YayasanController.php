<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Yayasan;
use Illuminate\Http\Request;

class YayasanController extends Controller
{
    public function edit()
    {
        return view('kelola.yayasan', ['yayasan' => Yayasan::firstOrFail()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['nama' => 'required|string|max:150', 'alamat' => 'nullable|string|max:500']);
        Yayasan::firstOrFail()->update($data);

        return back()->with('sukses', 'Data yayasan disimpan.');
    }
}
