<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    public function show(Lobby $lobby, SvgWriter $writer): Response
    {
        $qrCode = new QrCode(
            data: route('lobbies.join', $lobby),
            margin: 0,
        );

        $result = $writer->write($qrCode);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
        ]);
    }
}
