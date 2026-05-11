<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnggotaController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anggota = Anggota::all();

        return $this->successResponse(
            $anggota,
            "Anggota Retrieved Successfully",
            Response::HTTP_OK
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnggotaRequest $request)
    {
        $validated = $request->validated();
        $anggota = Anggota::create($validated);

        return $this->successResponse(
            $anggota,
            "Data Anggota Berhasil Ditambahkan",
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Anggota $anggota)
    {
        return $this->successResponse(
            $anggota,
            "Data Anggota Berhasil Ditemukan",
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anggota $anggota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnggotaRequest $request, Anggota $anggota)
    {
        $validated = $request->validated();
        $anggota->update($validated);
        return $this->successResponse(
            $anggota,
            "Data Anggota Berhasil Diubah",
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anggota $anggota)
    {
        $anggota->delete();

        return $this->successResponse(
            null,
            "Data Anggota Berhasil Dihapus",
            Response::HTTP_OK
        );
    }
}
