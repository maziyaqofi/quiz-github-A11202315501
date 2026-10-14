<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    // Menampilkan jadwal dokter yang sedang login
    public function index()
    {
        $jadwals = JadwalPeriksa::where('id_dokter', Auth::id())
            ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $validated['id_dokter'] = Auth::id();

        JadwalPeriksa::create($validated);

        return redirect()->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil ditambahkan')
            ->with('type', 'success');
    }

    // Menampilkan form edit jadwal
    public function edit(string $id)
    {
        $jadwal = JadwalPeriksa::where('id_dokter', Auth::id())
            ->findOrFail($id);

        return view('dokter.jadwal-periksa.edit', compact('jadwal'));
    }

    // Memperbarui jadwal periksa
    public function update(Request $request, string $id)
    {
        $jadwal = JadwalPeriksa::where('id_dokter', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $jadwal->update($validated);

        return redirect()->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil diperbarui')
            ->with('type', 'success');
    }

    // Menghapus jadwal periksa
    public function destroy(string $id)
    {
        $jadwal = JadwalPeriksa::where('id_dokter', Auth::id())
            ->findOrFail($id);

        $jadwal->delete();

        return redirect()->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil dihapus')
            ->with('type', 'success');
    }
}
