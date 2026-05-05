<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StorePembayaranRequest;
use App\Http\Requests\UpdatePembayaranRequest;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PembayaranController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembayaran = Pembayaran::all();

        return $this->successResponse(
            $pembayaran,
            "Pembayaran Retrieved Successfully",
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
    public function store(StorePembayaranRequest $request)
    {
        $validated = $request->validated();
        $pembayaran = Pembayaran::create($validated);

        return $this->successResponse(
            $pembayaran,
            "Data Pembayaran Berhasil Ditambahkan",
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembayaran $pembayaran)
    {
        return $this->successResponse(
            $pembayaran,
            Response::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembayaran $pembayaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePembayaranRequest $request, Pembayaran $pembayaran)
    {
        $validated = $request->validated();
        $pembayaran->update($validated);

        return $this->successResponse(
            $pembayaran,
            "Data Pembayaran Berhasil Diperbarui",
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();

        return $this->successResponse(
            null,
            "Data Pembayaran Berhasil Dihapus",
            Response::HTTP_NO_CONTENT
        );
    }
}
