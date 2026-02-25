<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Response;

class JurnalPrintController extends Controller
{
    public function printSingle(Jurnal $record)
    {
        $html = view('pdf.jurnal_single', ['jurnal' => $record])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=Jurnal-{$record->id}.pdf");
    }

    public function printBulk()
    {
        $ids = session()->get('print_ids');
        if (!$ids) return redirect()->back();

        $jurnals = Jurnal::with(['guru.user', 'mapel', 'kelas', 'media'])->whereIn('id', $ids)->get();
        $html = view('pdf.jurnal_bulk', ['jurnals' => $jurnals])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=Laporan-Kolektif.pdf');
    }
}
