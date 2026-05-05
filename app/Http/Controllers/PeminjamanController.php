<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StorePeminjamanRequest;
use App\Http\Requests\UpdatePeminjamanRequest;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeminjamanController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $peminjaman = Peminjaman::all();

        return $this->successResponse(
            $peminjaman,
            "Peminjaman Retrieved Successfully",
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
    public function store(StorePeminjamanRequest $request)
    {
        $validated = $request->validated();
        $peminjaman = Peminjaman::create($validated);

        return $this->successResponse(
            $peminjaman,
            "Data Peminjaman Berhasil Ditambahkan",
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Peminjaman $peminjaman)
    {
        return $this->successResponse(
            $peminjaman,
            Response::HTTP_OK
        );
    }

    /**StorePeminjamanRequest $request
     * Show the form for editing the specified resource.
     */
    public function edit(Peminjaman $peminjaman)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePeminjamanRequest $request, Peminjaman $peminjaman)
    {
        $validated = $request->validated();
        $peminjaman->update($validated);

        return $this->successResponse(
            $peminjaman,
            "Data Peminjaman Berhasil Diubah",
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        $peminjaman->delete();

        return $this->successResponse(
            null,
            "Data Peminjaman Berhasil Dihapus",
            Response::HTTP_NO_CONTENT
        );
    }
}
