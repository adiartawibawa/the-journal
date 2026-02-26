<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Settings\GeneralSettings;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class JurnalPrintController extends Controller
{
    public function printSingle(Jurnal $record, GeneralSettings $settings)
    {
        $html = view('pdf.jurnal_single', [
            'jurnal' => $record,
            'settings' => $settings,
        ])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=Jurnal-{$record->id}.pdf");
    }

    public function printBulk(GeneralSettings $settings)
    {
        $ids = session()->get('print_ids');
        if (!$ids) return redirect()->back();

        $jurnals = Jurnal::with(['guru.user', 'mapel', 'kelas', 'media'])
            ->whereIn('id', $ids)
            ->orderBy('tanggal', 'asc')
            ->get();

        $startDate = $jurnals->first()?->tanggal;
        $endDate = $jurnals->last()?->tanggal;

        $html = view('pdf.jurnal_bulk', [
            'jurnals' => $jurnals,
            'settings' => $settings,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=Laporan-Kolektif.pdf');
    }

    public function publicView(Jurnal $jurnal, GeneralSettings $settings)
    {
        return view('pdf.verif.jurnal_single', [
            'jurnal' => $jurnal,
            'settings' => $settings,
            'isPublic' => true
        ]);
    }

    public function publicBulkView(Request $request, GeneralSettings $settings)
    {
        $ids = explode(',', $request->query('ids'));

        $jurnals = Jurnal::with(['guru.user', 'mapel', 'kelas', 'media'])
            ->whereIn('id', $ids)
            ->orderBy('tanggal', 'asc')
            ->get();

        $startDate = $jurnals->first()?->tanggal;
        $endDate = $jurnals->last()?->tanggal;

        if (empty($ids)) {
            abort(404, 'Data tidak ditemukan.');
        }

        return view('pdf.verif.jurnal_bulk', [
            'jurnals' => $jurnals,
            'settings' => $settings,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'isPublic' => true
        ]);
    }
}
