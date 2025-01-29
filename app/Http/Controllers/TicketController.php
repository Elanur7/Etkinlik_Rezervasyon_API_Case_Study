<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Seat;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Mpdf\Mpdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

class TicketController extends Controller
{
    // Tüm biletleri listele
    public function index()
    {
        $tickets = Ticket::with('reservation', 'seat')->get();

        return response()->json([
            'status' => 'success',
            'tickets' => $tickets,
        ]);
    }

    // Belirli bir bileti getir
    public function show($id)
    {
        $ticket = Ticket::with('reservation', 'seat')->find($id);

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'ticket' => $ticket,
        ]);
    }

    // Bilet transferi yap
    public function transfer(Request $request, $id)
    {
        $request->validate([
            'to_reservation_id' => 'required|exists:reservations,id',
        ], [
            'to_reservation_id.required' => 'Rezervasyon ID alanı zorunludur.',
            'to_reservation_id.exists' => 'Geçersiz rezervasyon ID. Böyle bir rezervasyon bulunamadı.',
        ]);

        $reservationTo = Reservation::find($request->to_reservation_id);
        if (!$reservationTo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transfer yapmak istediğiniz rezervasyon bulunamadı.'
            ], 404);
        }

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        }

        // Kullanılmamış bilet kontrolü
        if ($ticket->status !== 'unused') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only unused tickets can be transferred',
            ], 400);
        }

        // Bilet transferi
        $ticket->reservation_id = $request->to_reservation_id;
        $ticket->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket transferred successfully',
            'ticket' => $ticket,
        ]);
    }

    // Bilet oluştur (örnek kod üretimi)
    public function create(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Yetkisiz işlem'], 403);
        }

        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'seat_id' => 'required|exists:seats,id',
        ], [
            'reservation_id.required' => 'Rezervasyon ID zorunludur.',
            'reservation_id.exists' => 'Geçersiz rezervasyon ID. Böyle bir rezervasyon bulunamadı.',
            'seat_id.required' => 'Koltuk ID zorunludur.',
            'seat_id.exists' => 'Geçersiz koltuk ID. Böyle bir koltuk bulunamadı.',
        ]);

        // Reservation kontrolü
        $reservation = Reservation::find($request->reservation_id);
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Geçersiz rezervasyon ID.'
            ], 404);
        }

        // Seat kontrolü
        $seat = Seat::find($request->seat_id);
        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Geçersiz koltuk ID.'
            ], 404);
        }

        $ticketCode = strtoupper(bin2hex(random_bytes(4))); // Benzersiz 8 karakterli kod

        $ticket = Ticket::create([
            'reservation_id' => $request->reservation_id,
            'seat_id' => $request->seat_id,
            'ticket_code' => $ticketCode,
        ]);

        return response()->json([
            'status' => 'success',
            'ticket' => $ticket,
        ]);
    }

    public function cancelTicket($id)
    {
        // İlgili bileti bulun
        $ticket = Ticket::with('event')->find($id);

        // Bilet kontrolü
        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found.',
            ], 404);
        }

        // Etkinlik tarihi kontrolü
        $eventStartDate = Carbon::parse($ticket->event->start_date); // Etkinlik başlangıç tarihi
        $currentDate = Carbon::now(); // Şu anki tarih

        if ($currentDate->diffInHours($eventStartDate, false) < 24) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tickets can only be canceled up to 24 hours before the event start date.',
            ], 400);
        }

        // Biletin iptal durumu kontrolü
        if ($ticket->status === 'canceled') {
            return response()->json([
                'status' => 'error',
                'message' => 'This ticket is already canceled.',
            ], 400);
        }

        // Bileti iptal et
        $ticket->status = 'canceled';
        $ticket->save();

        return response()->json([
            'status' => 'success',
            'message' => 'The ticket has been successfully canceled.',
        ], 200);
    }

    public function download($id)
    {
        // Bilet tablosundan bilet ID'yi buluyoruz
        $ticket = Ticket::findOrFail($id);

        // Eğer bilet rezervasyonla ilişkilendirilmişse rezervasyon bilgilerini alıyoruz
        $reservation = $ticket->reservation;

        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found for this ticket.'
            ], 404);
        }

        // expires_at'ı doğru şekilde formatlıyoruz
        $expires_at = new \DateTime($reservation->expires_at); // Eğer expires_at bir tarih ise
        $expires_at_formatted = $expires_at->format('Y-m-d H:i:s'); // Tarihi formatlıyoruz

        // PDF içeriğini oluşturuyoruz
        $html = '
    <html>
        <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4a90e2;
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            margin: 10px 0;
        }
        .details {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
        }
        .details p {
            margin: 8px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reservation Ticket</h1>
        <div class="details">
            <p><strong>Event:</strong> ' . htmlspecialchars($reservation->event->name) . '</p>
                <p><strong>User:</strong> ' . htmlspecialchars($reservation->user->name) . '</p>
                <p><strong>Total Amount:</strong> ' . htmlspecialchars($reservation->total_amount) . ' USD</p>
                <p><strong>Expires At:</strong> ' . htmlspecialchars($expires_at_formatted) . '</p>
                <p><strong>Status:</strong> ' . htmlspecialchars($reservation->status) . '</p>
               </div>
        <div class="footer">
            <p>Thank you for your reservation!</p>
            <p>For assistance, contact us at support@example.com</p>
        </div>
    </div>
</body>
    </html>';

        // Dompdf seçeneklerini ayarlıyoruz
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        // Dompdf instance oluşturuyoruz
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Sayfayı render ediyoruz
        $dompdf->render();

        // PDF dosyasını indirme olarak döndürüyoruz
        return response()->stream(
            function () use ($dompdf) {
                echo $dompdf->output(); // PDF içeriğini çıktı olarak verir
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ticket.pdf"',
            ]
        );
    }
}
